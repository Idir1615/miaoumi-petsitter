"""Test HTTP isolé : python tests/auth_smoke.py C:\\xampp\\php\\php.exe.

Utilise SQLite temporaire ; ne remplace pas la validation sur MySQL.
"""
import http.cookiejar
import pathlib
import re
import socket
import sqlite3
import subprocess
import sys
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

ROOT = pathlib.Path(__file__).resolve().parents[1]
PHP = sys.argv[1] if len(sys.argv) > 1 else 'php'


def run():
    with tempfile.TemporaryDirectory(prefix='miaoumi-auth-') as temporary:
        temporary = pathlib.Path(temporary)
        database = temporary / 'test.sqlite'
        with sqlite3.connect(database) as db:
            db.executescript('''
                CREATE TABLE utilisateurs (id INTEGER PRIMARY KEY, nom TEXT,
                    email TEXT UNIQUE, password TEXT, role TEXT DEFAULT 'user');
                CREATE TABLE annonces (id INTEGER PRIMARY KEY, titre TEXT,
                    type_animal TEXT, date_debut TEXT, date_fin TEXT);
            ''')
        db.close()
        bootstrap = temporary / 'router.php'
        # Injecter uniquement dans le serveur de test ; aucune configuration réelle modifiée.
        bootstrap.write_text('<?php\nrequire_once ' + repr((ROOT / 'core/Database.php').as_posix())
            + ';\n$pdo = new PDO(' + repr('sqlite:' + database.as_posix())
            + ', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);'
            + '\n$p = new ReflectionProperty(Database::class, "pdo"); $p->setAccessible(true); $p->setValue(null, $pdo);'
            + '\nrequire ' + repr((ROOT / 'public/index.php').as_posix()) + ';', encoding='utf-8')
        with socket.socket() as sock:
            sock.bind(('127.0.0.1', 0))
            port = sock.getsockname()[1]
        base = f'http://127.0.0.1:{port}/?page='
        jar = http.cookiejar.CookieJar()
        client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))

        def request(page, fields=None):
            data = None if fields is None else urllib.parse.urlencode(fields).encode()
            try:
                response = client.open(base + page, data, timeout=5)
            except urllib.error.HTTPError as error:
                response = error
            return response.status, response.read().decode('utf-8')

        def token(html):
            return re.search(r'name="csrf" value="([a-f0-9]+)"', html).group(1)

        with (temporary / 'server.log').open('w') as output:
            server = subprocess.Popen([PHP, '-d', f'session.save_path={temporary}', '-S', f'127.0.0.1:{port}', str(bootstrap)],
                                      cwd=ROOT, stdout=output, stderr=output)
            try:
                for attempt in range(40):
                    try:
                        status, html = request('inscription')
                        break
                    except urllib.error.URLError:
                        time.sleep(.1)
                else:
                    raise RuntimeError('Le serveur de test ne démarre pas.')
                assert status == 200
                csrf = token(html)
                assert request('inscription', {'csrf': 'incorrect'})[0] == 403
                fields = dict(csrf=csrf, nom='<Alice>', email='alice@example.test',
                              password='Test-Secret-123', confirmation='different')
                status, validation = request('inscription', fields)
                assert 'ne correspondent pas' in validation, (status, validation)
                fields['confirmation'] = fields['password']
                status, html = request('inscription', fields)
                assert status == 200 and 'Ton compte a été créé' in html
                with sqlite3.connect(database) as db:
                    stored, role = db.execute('SELECT password, role FROM utilisateurs').fetchone()
                    assert stored != fields['password'] and stored.startswith('$2') and role == 'user'
                db.close()
                login = dict(csrf=token(html), email=fields['email'], password='incorrect')
                assert 'E-mail ou mot de passe incorrect' in request('connexion', login)[1]
                old_session = next(c.value for c in jar if c.name == 'PHPSESSID')
                login['password'] = fields['password']
                status, html = request('connexion', login)
                assert status == 200 and 'Bonjour &lt;Alice&gt;' in html
                assert next(c.value for c in jar if c.name == 'PHPSESSID') != old_session
                assert request('deconnexion')[0] == 405
                assert request('deconnexion', {'csrf': csrf})[0] == 403
                status, html = request('deconnexion', {'csrf': token(html)})
                assert status == 200 and 'Bonjour' not in html
                assert 'Bonjour' not in request('annonces')[1]
                assert request('inconnue')[0] == 404
                print('OK : inscription, hachage, validation, connexion, session, CSRF, échappement et déconnexion (SQLite isolé).')
            finally:
                server.terminate()
                server.wait(timeout=5)


if __name__ == '__main__':
    run()
