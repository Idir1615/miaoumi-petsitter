# Consignes de travail — Miaoumi

- Lire le README et les fichiers concernés avant de modifier le projet.
- Travailler sur la branche demandée et préserver les modifications de l’utilisateur.
- Branches : circuit `feature/prénom-fonctionnalité` → `develop` → `main`. Les branches `feature/idir-…`, `feature/ama-…`, `feature/maria-…` partent de `develop` et y reviennent par pull request validée par Idir ; seul Idir fusionne `develop` dans `main`. Ama et Maria ne poussent jamais sur `develop` ni sur `main`.
- Pour chaque changement significatif, ajouter un résumé en français sous « Non publié » dans CHANGELOG.md. Regrouper les ajustements d’une même tâche.
- Dater chaque entrée du CHANGELOG au format JJ/MM/AAAA à HH:MM, en heure de Paris (Europe/Paris). Pour une ancienne entrée, utiliser l’horodatage Git en précisant qu’il s’agit du commit ; ne pas inventer l’heure réelle de modification. Pour le travail non commité, indiquer l’heure de consignation.
- Équipe : Idir, Ama et Maria. Utiliser uniquement les prénoms. Ajouter le prénom de la personne ayant réalisé ou piloté le changement dans chaque entrée du CHANGELOG. Les changements de cette conversation sont pilotés par Idir avec l’assistant. Pour les contributions futures, confirmer l’identité via le contexte ou une correspondance Git connue ; sinon indiquer « Auteur à confirmer », sans attribuer par défaut à Idir ni inventer une contribution d’Ama ou Maria.
- Mettre à jour README.md si l’installation, les fonctionnalités ou la structure changent.
- Ne pas inventer de fonctionnalités, de tests réussis ou de dates de publication.
- Vérifier les changements avec les outils disponibles et signaler les vérifications impossibles.
- Ne jamais enregistrer de mots de passe, de configuration locale ou de données personnelles dans les journaux.
- Ne pas modifier manuellement les historiques générés dans logs/.
- Ne pas créer de commit ni envoyer vers GitHub sans demande de l’utilisateur.
