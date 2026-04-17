# MAILER_DSN

Ce projet utilise Symfony Mailer.

La configuration active est ici :
- [mailer.yaml](/Users/ludwigelatre/Desktop/siyag-projet/siyag/config/packages/mailer.yaml)

La variable utilisée est :
- `MAILER_DSN`

## Comportement actuel

Dans `.env`, la valeur par défaut est :

```env
MAILER_DSN=null://null
```

Avec cette valeur :
- aucun email réel n'est envoyé
- le formulaire de contact fonctionne côté application
- mais rien ne part vers une boîte mail

## Configuration locale

Le plus propre est de définir `MAILER_DSN` dans `.env.local`.

Ce projet dispose deja d'un conteneur Mailpit dans la stack Docker :

```yaml
mailer:
  image: axllent/mailpit
  ports:
    - "1025"
    - "8025"
```

Important :
- si Symfony tourne sur ta machine locale, il faut utiliser le port hote mappe par Docker
- il ne faut pas supposer que ce sera toujours `1025`

Exemple avec le mapping visible dans Docker Desktop :

```text
51487:1025
```

Dans ce cas, il faut mettre :

```env
MAILER_DSN=smtp://127.0.0.1:51487
```

Si Symfony tourne dans un conteneur du meme `docker compose`, alors il faut viser directement le service Mailpit sur le reseau Docker :

```env
MAILER_DSN=smtp://mailer:1025
```

Exemple avec un SMTP authentifié :

```env
MAILER_DSN=smtp://USERNAME:PASSWORD@smtp.example.com:587
```

Si le mot de passe contient des caractères spéciaux, il faut l'encoder en URL.

## Configuration production

En production, il faut injecter une vraie valeur `MAILER_DSN` via :
- variables d'environnement du serveur
- secrets du pipeline
- ou configuration d'hébergement

Exemple :

```env
MAILER_DSN=smtp://USERNAME:PASSWORD@smtp.example.com:587
```

## Comment verifier

Le formulaire contact envoie via :
- [ContactController.php](/Users/ludwigelatre/Desktop/siyag-projet/siyag/src/Controller/ContactController.php)
- [ContactService.php](/Users/ludwigelatre/Desktop/siyag-projet/siyag/src/Service/ContactService.php)

Le destinataire configuré est :
- `CONTACT_EMAIL`
- l'expéditeur technique est `MAILER_FROM_EMAIL`

Valeur par défaut dans [`.env`](/Users/ludwigelatre/Desktop/siyag-projet/siyag/.env) :

```env
CONTACT_EMAIL=contact@siyaj-editions.fr
MAILER_FROM_EMAIL=no-reply@example.com
```

Le plus simple pour tester en local :
1. définir `MAILER_DSN` dans `.env.local`
2. lancer l'application
3. envoyer un message depuis `/contact`
4. ouvrir l'interface Mailpit sur le port HTTP mappe par Docker

Exemple :
- si Docker affiche `51488:8025`
- alors l'interface Mailpit est disponible sur `http://127.0.0.1:51488`

## Remarque utile

Le formulaire contact utilise un `replyTo` avec l'email saisi par l'utilisateur.

Donc :
- l'email arrive sur `CONTACT_EMAIL`
- l'email part techniquement depuis `MAILER_FROM_EMAIL`
- en répondant depuis la boîte mail, la réponse repart vers l'expéditeur du formulaire

Exemple de configuration Gmail :

```env
MAILER_DSN=smtp://siyaj.editions%40gmail.com:APP_PASSWORD@smtp.gmail.com:587?encryption=tls&auth_mode=login
MAILER_FROM_EMAIL=contact@siyaj-editions.fr
CONTACT_EMAIL=siyaj.editions@gmail.com
```

Important :
- si `MAILER_FROM_EMAIL` n'est pas une adresse/alias autorisé(e) par le compte Gmail qui envoie, Gmail peut réécrire l'expéditeur ou refuser l'envoi
- le plus fiable est d'ajouter cette adresse comme alias dans Gmail, ou d'utiliser comme `MAILER_FROM_EMAIL` l'adresse Gmail elle-même
