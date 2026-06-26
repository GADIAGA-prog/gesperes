# Publier l'Espace agent sur le Play Store (Android)

L'Espace agent est une **PWA installable**. Pour le **Play Store**, on l'empaquette en
**TWA** (*Trusted Web Activity*) : une fine app Android qui ouvre la PWA en plein écran,
**sans barre d'adresse**, à condition que l'association app ↔ site (Digital Asset Links)
soit vérifiée.

> Aucune réécriture : c'est la même application web. L'app Android n'est qu'une coquille
> qui pointe vers le domaine de production.

---

## 0. Prérequis

- La PWA est **déployée en HTTPS** (Laravel Cloud : déjà le cas — l'install PWA exige HTTPS).
- Domaine de production connu, noté ci-dessous `VOTRE-DOMAINE-PROD`.
- Un compte **Google Play Console** (frais uniques de 25 USD) pour publier.
- Vérifier que ces URL répondent en prod :
  - `https://VOTRE-DOMAINE-PROD/manifest.webmanifest`
  - `https://VOTRE-DOMAINE-PROD/images/icons/icon-512.png`
  - `https://VOTRE-DOMAINE-PROD/.well-known/assetlinks.json` (renvoie `[]` tant que l'empreinte n'est pas renseignée — normal)

---

## 1. Générer le paquet Android — deux options

### Option A — PWABuilder (recommandé, sans installer Android SDK)

1. Aller sur **https://www.pwabuilder.com** et saisir `https://VOTRE-DOMAINE-PROD/espace-agent`.
2. Lancer l'analyse (le score PWA doit être bon : manifest + service worker OK).
3. Cliquer **Package For Stores → Android → Generate**.
4. Conserver les options par défaut (package id : `bf.gov.mesfpt.gesperes`).
5. Télécharger le `.zip` : il contient
   - `app-release-signed.aab` (à téléverser sur le Play Store),
   - le **keystore de signature** (`signing.keystore`) + ses mots de passe (`signing-key-info.txt`) — **à conserver précieusement**,
   - le fichier `assetlinks.json` **avec l'empreinte SHA-256**.

### Option B — Bubblewrap (CLI, contrôle total)

Prérequis : Node 18+, **JDK 17**, Android SDK.

```bash
npm i -g @bubblewrap/cli
bubblewrap init --manifest https://VOTRE-DOMAINE-PROD/manifest.webmanifest
# (le gabarit twa-manifest.json à la racine du dépôt sert de référence)
bubblewrap build
```

Sorties : `app-release-bundle.aab` (à téléverser) et un keystore `android.keystore`.
Récupérer l'empreinte :

```bash
bubblewrap fingerprint list
# ou : keytool -list -v -keystore android.keystore -alias gesperes
```

---

## 2. Activer l'association app ↔ site (supprime la barre d'adresse)

L'app sert déjà `/.well-known/assetlinks.json` **dynamiquement** depuis la config. Il suffit
de renseigner l'empreinte de signature dans l'environnement de production :

```dotenv
ANDROID_PACKAGE_NAME=bf.gov.mesfpt.gesperes
# Empreinte SHA-256 obtenue à l'étape 1 (format AA:BB:CC:...).
# Plusieurs empreintes possibles, séparées par des virgules (voir note Play App Signing).
ANDROID_SHA256_FINGERPRINT=AA:BB:CC:DD:...
```

Puis vider le cache de config en prod (`php artisan config:clear` / redéploiement) et vérifier :

```
https://VOTRE-DOMAINE-PROD/.well-known/assetlinks.json
```

…doit maintenant renvoyer un objet avec `package_name` et `sha256_cert_fingerprints`.

> **Play App Signing (important).** Si vous activez « Play App Signing » (recommandé par
> Google), le Play Store **re-signe** votre app avec **sa propre clé**. L'empreinte qui
> compte alors est celle affichée dans **Play Console → Configuration → Intégrité de l'app →
> Certificat de la clé de signature de l'app** (SHA-256). Mettez **les deux** empreintes
> (clé d'upload **et** clé d'app) dans `ANDROID_SHA256_FINGERPRINT`, séparées par des virgules.

---

## 3. Publier sur le Play Console

1. **Créer l'application** (nom : *GesPerES — Espace agent*, langue : français).
2. **Téléverser le `.aab`** dans un canal (test interne d'abord, puis production).
3. Renseigner la fiche : description, captures d'écran (téléphone), icône (512×512 — voir
   `public/images/icons/icon-512.png`), catégorie *Administration / Outils*, politique de
   confidentialité, coordonnées.
4. Remplir le questionnaire **Contenu de l'app** (accès aux données, public visé).
5. Soumettre à l'examen. Délai habituel : de quelques heures à quelques jours.

---

## 4. Mises à jour

L'app n'embarque **pas** le contenu : toute évolution déployée sur le site est **immédiate**
dans l'app. Ne republier sur le Play Store que pour changer l'icône, le nom, les couleurs ou
le comportement de la coquille (incrémenter alors `appVersionCode`).

---

## Récapitulatif des éléments déjà en place dans le dépôt

| Élément | Emplacement |
|---|---|
| Manifeste PWA (`id`, icônes maskables, scope) | `public/manifest.webmanifest` |
| Service worker (hors-ligne, cache assets) | `public/sw.js` |
| Icônes (192 / 512 / apple) | `public/images/icons/` (régénérables : `php scripts/generer-icones-pwa.php`) |
| Digital Asset Links (dynamique via config) | route `/.well-known/assetlinks.json` → `AssetLinksController` |
| Config package + empreinte | `config/gesperes.php` (`android.*`) via `.env` |
| Gabarit Bubblewrap | `twa-manifest.json` |
