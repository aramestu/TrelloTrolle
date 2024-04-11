---
lang: fr
---
# SAE4A - Trello-Trollé

Nécessite une verion de PHP supérieur à 8.2
L'hébéregement sur les serveurs du BUT était impossible, il faut donc le lancer en localhost après avoit installer les différentes librairies via le composer.json.

Liste des fonctionnalités améliorées : 
* Changement de la base de données
* Refactor des data objects et des repository pour fonctionner avec la nouvelle base de données
* Séparation de la couche métiers des controller : ajout des services
* Ajout d'un routeur URL
* Ajout du Lazy Loading
* Remplacement des vues php par des vues twig
* Refactor de la librairie de mot de passe
* Ajout du tableau dynamique
