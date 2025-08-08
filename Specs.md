# Spécifications du Projet : Voisin'Partage

## 1. Description du projet
"Voisin'Partage" est une application web (responsive, mobile-first) destinée au marché québécois, visant à faciliter le prêt et l'emprunt d'objets et d'outils entre voisins. Le système utilise un périmètre géographique pour définir le voisinage et intègre un processus de transaction sécurisé.

## 2. Technologies
- Plateforme : Application web SPA (Vue.js 3 + Vite), responsive.
- Backend : PHP (framework Laravel).
- Base de données : MySQL.
- Cartographie : OpenStreetMap via Leaflet.
- Paiement : Stripe (séquestre/autorisation et capture différée, ou Connect selon besoin).
- Sécurité : HTTPS, gestion des secrets via .env, conformité RGPD (droit d’accès/suppression), validations côté serveur.
- Notifications : E-mails (Laravel Notifications) et notifications web push (Service Worker) ou temps réel (Laravel Echo/Pusher).
- CI/CD & Build : Composer, Node.js (npm/pnpm), Vite, tests (PHPUnit/Pest) et lint (ESLint/Prettier, PHPStan/Psalm).

## 3. Fonctionnalités et Règles de gestion
### A. Inscription et Profil
- Le formulaire d'inscription demande : pseudo, adresse e-mail, adresse, code postal et téléphone canadien.
- La géolocalisation navigateur (HTML5) est activée lors de l'inscription pour définir le centre du cercle de voisinage.
- L'application fonctionne uniquement au Québec. Un GPS/précision navigateur imprécis empêche l'utilisation.
- La position peut être mise à jour par l'utilisateur une fois tous les 3 mois.
- Lors de l'inscription, le code postal et la localisation GPS sont vérifiés pour garantir que l'utilisateur est bien situé au Québec.
- En cas de GPS imprécis ou incohérent, l'accès à l'application est bloqué et une vérification manuelle peut être demandée.

### B. Authentification
- Connexion par formulaire (e-mail et mot de passe) ou via Google.
- Un processus de récupération de mot de passe est intégré.

### C. Carte et Voisinage
- Un cercle de 2 km de rayon est dessiné autour de la position de l'utilisateur. La zone intérieure est colorée, la zone extérieure est grisée.
- Seuls les articles disponibles à l'intérieur du cercle sont affichés.
- Les articles disponibles sont indiqués par des points verts sur la carte.

### D. Gestion des articles et de la recherche
- Les membres peuvent proposer un article à prêter.
- Chaque article a un statut "disponible" ou "non disponible".
- Le statut est mis à jour automatiquement dès qu'une demande de prêt est acceptée.
- La recherche d'articles est instantanée via un champ de texte. Un bouton "Détails" s'affiche pour chaque résultat.
- Les articles proposés sont soumis à une modération automatique et/ou manuelle avant publication pour garantir leur conformité (photos, descriptions, etc.).

### E. Notifications
- Un système d’e-mails et de notifications web push informe les utilisateurs à chaque étape importante (demande de prêt, acceptation, retour d'objet, litige, etc.).

## 4. Flux de transaction et de paiement
### A. Demande de prêt
- La page de demande d'emprunt affiche les détails de l'article, les profils des membres et les informations du prêt.
- Le propriétaire peut accepter ou refuser la demande.

### B. Paiement (via Stripe avec séquestre)
- Le paiement est effectué au moment de la validation de la demande par le propriétaire.
- L'argent est retenu en séquestre par Stripe et n'est pas versé immédiatement au propriétaire.
- Les frais de transaction sont à la charge de l'emprunteur.
- Les fonds sont débloqués à la fin du contrat, une fois le retour de l'objet confirmé par les deux parties.
- En cas d'annulation avant l'échange, l'emprunteur est automatiquement remboursé.

## 5. Système d'avis et de gestion des litiges
### A. Avis
- Un avis peut être laissé par chaque membre après la complétion d'une transaction.
- Les avis sont publics et non anonymes.
- La note moyenne (1 à 5 étoiles) est affichée sur le profil de chaque utilisateur.

### B. Litiges
- Les litiges sont gérés par un administrateur via une adresse e-mail (client@voisinpartage.com).
- L'administrateur suit un protocole strict de collecte de preuves avant de prendre une décision.
- Les fonds en séquestre sont débloqués selon la décision de l'administrateur, basée sur des critères clairs :
    - Annulation : Remboursement total de l'emprunteur.
    - Objet endommagé ou non conforme : Remboursement total ou partiel de l'emprunteur, ou dédommagement du propriétaire.
    - Non-retour de l'objet : Versement total des fonds au propriétaire à titre de dédommagement initial.

## 6. Architecture et déploiement (web)

### A. Architecture cible
- Frontend SPA Vue.js 3 (Vite) consommant une API REST Laravel (Sanctum pour l’auth SPA).
- Backend Laravel (contrôleurs REST, Eloquent ORM, Jobs/Queues pour e-mails et modération).
- Stockage fichiers (images) via Laravel Filesystem (local/S3).
- Notifications : Laravel Notifications (mail) + Web Push (service worker) ou temps réel (Echo/Pusher).

### B. Déploiement
- Environnements: Dev (Docker/Laragon), Staging, Prod.
- Serveur web: Nginx/Apache, PHP-FPM 8.2+.
- Build frontend: Vite (npm run build), assets servis statiquement.
- CI/CD: ex. GitHub Actions (composer install, npm ci, tests, build, déploiement).

### C. Décisions clés (choix modernes par défaut)
- Versions & stack
   - Backend: Laravel 11, PHP 8.3, MySQL 8 (types spatiaux), Redis (queues/cache), Pest (tests), PHPStan niveau 6.
   - Frontend: Vue 3 + TypeScript, Vite, Pinia, Vue Router, Axios, Tailwind CSS, vue-i18n, vue-leaflet.
   - Temps réel: Laravel WebSockets (bref/self-host) ou Pusher (prod rapide). Par défaut: Laravel WebSockets.

- Cartographie & géolocalisation
   - Tuiles: Dev → OpenStreetMap; Prod → MapTiler (clé API) via Leaflet. Attribution conforme obligatoire.
   - Géolocalisation navigateur (HTML5) avec précision minimale acceptée ≤ 100 m; sinon fallback géocodage code postal.
   - Zone voisinage: rayon par défaut 2 km, ajustable par l’admin (1–10 km), verrouillé par utilisateur (maj tous 3 mois).
   - Requêtes géo: colonne POINT(lat, lon) SRID 4326 + INDEX SPATIAL; filtrage Haversine (ST_Distance_Sphere) + bounding box.

- Paiement (Stripe)
   - Stripe Connect Express pour marketplace (onboarding propriétaire, KYC géré par Stripe).
   - Modèle charges: destination charges (+ application_fee_amount) avec transfers différés au propriétaire après confirmation de retour.
   - « Séquestre »: capture immédiate des fonds sur la plateforme, transfert retardé (quasi-séquestre). Annulation/retour → remboursement partiel/total selon règles.
   - Option dépôt de garantie (PaymentIntent dédié, annulé ou partiellement capturé en cas de dommage).

- Authentification & identité
   - Auth SPA via Laravel Sanctum, CSRF sécurisé; e-mail vérifié obligatoire.
   - OAuth Google via Socialite (optionnel à l’inscription/connexion).
   - 2FA TOTP (optionnel) pour propriétaires à partir d’un certain volume.
   - Téléphone: champ optionnel; OTP SMS possible (Twilio) en phase 2.

- Contenu & médias
   - Upload images → S3 compatible (ex. Cloudflare R2), redimensionnement/optimisation en queue (Intervention Image / Glide).
   - Formats: JPEG/PNG/WebP; taille max 5 Mo/6 photos par article; suppression EXIF.
   - Modération: automatique (règles mime/taille/mots interdits) + manuelle via back-office.

- Notifications
   - E-mails via Laravel Notifications + queue.
   - Web Push (VAPID) pour navigateurs compatibles; fallback e-mail. Événements: demande, acceptation, remise, retour, litige.
   - Préférences utilisateur par canal/événement.

- Conformité & sécurité
   - Loi 25 (Québec) + RGPD: consentements, registre finalités, droit d’accès/suppression, politique de rétention.
   - Sécurité: rate limiting, CSP (spatie/laravel-csp), headers sécurité, audit logs, reCAPTCHA v3 sur endpoints sensibles.
   - Données sensibles en .env; rotation des clés; sauvegardes chiffrées.

- Expérience produit
   - PWA installable (Service Worker, manifest), cache offline limité (shell + écran liste), background sync pour petites MAJ.
   - Accessibilité: WCAG 2.1 AA; i18n fr-CA par défaut, en-CA en option.
   - Recherche: plein texte (MySQL FT) et tags; clustering marqueurs si >500 points (markercluster).

## 7. Plan d’action

1. Mise en place de l’infrastructure
   - Initialiser un projet Laravel (PHP 8.2+), config MySQL, .env
   - Ajouter frontend Vue 3 + Vite (Inertia ou API + SPA au choix; par défaut SPA consommant API)
   - Configurer CI/CD (Composer, npm, tests, build)
   Test : `php artisan test` OK ; `npm run build` OK ; migrations appliquées.

2. Gestion des utilisateurs
   - Étendre la migration User (adresse, code postal, lat, lon, date_maj_position)
   - API d’inscription et vérification QC + géolocalisation navigateur
   - UI Vue: formulaires d’inscription/profil
   Test : inscription hors Québec rejetée (regex FSA G/H/J) ; MAJ position limitée à 12 semaines.

3. Authentification
   - Sanctum pour session SPA (CSRF) ; endpoints login/reset ; Socialite pour Google OAuth
   - UI Vue: login, mot de passe oublié, OAuth Google
   Test : flux login OK/KO, reset e-mail fonctionnel, OAuth OK.

4. Carte et voisinage
   - Leaflet avec cercle 2 km (zone intérieure colorée, extérieure grisée)
   - Endpoint REST Laravel: articles filtrés par rayon (requête géo basique Haversine)
   - UI Vue: carte + marqueurs verts
   Test : hors rayon non renvoyés ; rendu carte conforme.

5. CRUD Articles et modération
   - Modèle Eloquent Article (photos, description, statut, owner)
   - Endpoints création, modération, statut, détails (+ upload images, validation MIME/taille)
   - UI Vue: recherche instantanée, liste, détail
   Test : nouvel article non publié avant modération ; recherche correcte.

6. Notifications
   - E-mails via Laravel Notifications + queue ; web push (Push API) ou Echo/Pusher
   - Déclencheurs : demande, acceptation, retour, litige
   Test : réception e-mails + push/temps réel à chaque étape.

7. Transactions et paiement
   - Modèle Transaction ; intégration Stripe (PaymentIntent capture différée / Connect si besoin)
   - Endpoints: initier, confirmer/capturer, rembourser, débloquer
   - UI Vue: paiement et suivi
   Test : paiement→séquestre→déblocage ; annulation avant échange = remboursement.

8. Avis et litiges
   - Modèle Review et moyenne étoiles sur profil
   - Interface admin litiges (upload preuves, décision)
   - Logique déblocage/remboursement partiel selon décision
   Test : création d’avis et calcul de moyenne; scénarios de litige simulés.

9. Sécurité & conformité
   - HTTPS, RGPD (export/suppression données, consentement)
   - Validation server-side, throttling (rate limit), journalisation
   Test : tentatives d’injection bloquées; export/suppression fonctionnels.

