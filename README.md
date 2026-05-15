# App de sondages (Laravel + Vue)

Mini-projet du cours DévProdMéd (HEIG-VD).

## Présentation

L'application permet à une personne connectée de créer des sondages, d'en définir les options de réponse et de configurer leur comportement : choix simple ou multiple, résultats publics ou privés, durée de disponibilité.

Une fois le sondage lancé, un lien de partage contenant un token secret est disponible. Les personnes qui ouvrent ce lien peuvent voter (si connectées) et consulter les résultats si le créateur les a rendus publics.

Les résultats s'affichent en direct via un polling toutes les 5 secondes, avec une visualisation sous forme de barres de progression.

## Installer

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate
```

Données de test (optionnel) :
```bash
php artisan db:seed
```

## Lancer

```bash
composer run dev
```

Puis ouvrir http://localhost:8000.

## Utilisation

Le dashboard des sondages est accessible via `/polls/dashboard` (connexion requise).

Le système d'authentification (inscription, connexion, déconnexion) faisait déjà partie du projet de base et n'a pas été modifié.

## Choix techniques

### Deux applications Vue distinctes

Le projet utilise deux points d'entrée Vue séparés (`poll-dashboard.js` et `poll-vote.js`) plutôt qu'une application unique avec routeur. Le dashboard est protégé par authentification, tandis que la page de vote est publique. Cette séparation évite de mettre en place un routeur pour gérer deux contextes très différents.

### Store via composable plutôt que Pinia

L'état global est géré par un composable `usePollStore` utilisant des `ref()` partagées, comme présenté dans le cours. Cette approche est plus légère pour un projet avec un seul store et évite une dépendance supplémentaire.

### `useFetchApi` pour toutes les requêtes

Le projet de base fournissait deux systèmes de fetch (`useFetchApi` et `useFetchJson`). Toutes les requêtes vers l'API utilisent `useFetchApi`, dont le pattern `fetchApi({ url, method, data })` permet de gérer simplement les opérations GET, POST, PATCH et DELETE.

### Polling pour les résultats

Le composable `usePolling` fourni dans le cours est utilisé avec un intervalle de 5 secondes. Lorsque le sondage est en cours, le frontend appelle régulièrement l'API pour rafraîchir les données. Le polling s'arrête automatiquement au démontage du composant grâce à `onUnmounted`.

### Pas de hash routing

Le cours présentait `useHashRoute` pour naviguer entre composants dans une même application Vue. Ce besoin ne s'est pas présenté : le dashboard alterne entre deux vues (liste et formulaire) avec un simple `v-if`, et la page de vote a un affichage linéaire sans navigation interne.

### Architecture des endpoints

Tous les endpoints API sont regroupés sous `/api/v1/polls`. Les routes protégées (création, modification, suppression, vote) utilisent le middleware `auth:sanctum` pour l'authentification par session. La route `show` (`GET /api/v1/polls/{token}`) reste publique afin que les personnes non connectées puissent accéder au sondage.

Les résultats (avec le décompte des votes) ne sont inclus dans la réponse que si le sondage est marqué comme public ou si la personne qui consulte en est la créatrice. Dans le cas contraire, seules les options sont renvoyées, sans les votes.

### Vérification de la date de fin côté serveur

Le statut "sondage terminé" est calculé côté backend (champ `is_ended` ajouté à la réponse de `show`) plutôt que côté frontend. Cela évite les soucis de fuseau horaire entre le format SQLite renvoyé par Eloquent et l'interprétation locale de `new Date()` en JavaScript.

### Entrées Vite séparées

Le fichier `vite.config.js` déclare deux entrées distinctes (`poll-dashboard.js` et `poll-vote.js`). Chaque page Blade ne charge que le JavaScript dont elle a besoin via la directive `@vite`.
