# 🎯 Match-app

Match-app est une application de rencontre développée avec Symfony. Elle propose un système de chat en temps réel et des notifications instantanées grâce à **Mercure via Docker**. L'application met l'accent sur la **sécurité** et la **pertinence des correspondances** grâce à un processus de validation et un questionnaire d'affinité.

---

## 🚀 Fonctionnalités

### 🔑 Authentification et Inscription
✅ Formulaire de connexion et inscription
✅ Validation de l'identité par un administrateur avant accès (spoiler la page des admins est sur la route 127.0.0.1:8000/admin/id-verification)

### 🛡️ Vérification d'identité
✅ Contrôle manuel de la pièce d'identité par un administrateur
✅ Accès restreint tant que l'identité n'est pas validée

### 📝 Questionnaire Inou
✅ Série de questions pour mieux cerner l'utilisateur
✅ Amélioration du matching grâce aux réponses

### 🏠 Page d'accueil
✅ Affichage des matchs les plus compatibles
✅ Conversations en cours directement accessibles

### 💬 Chat en temps réel
✅ Utilisation de **Mercure** pour les messages instantanés
✅ Notifications en temps réel des nouveaux messages

---

## 🛠 Prérequis

- PHP 8+
- Symfony 6+
- Composer
- Docker & Docker Compose
- Mercure
- Base de données (MySQL, PostgreSQL, SQLite...). 
## PS : Nous utilisons nous une base de donnée en ligne avec mongoDB

---

## 📦 Installation

1️⃣ **Cloner le dépôt**
```sh
git clone https://github.com/CODEBYGODWIN/DevProject
cd Match-app
```

2️⃣ **Installer les dépendances**
```sh
composer install
```

3️⃣ **Configurer l'environnement**
- Copier `.env.example` en `.env`
- Modifier les variables de connexion à la base de données et à Mercure

4️⃣ **Lancer les services Docker ou simplement mercure**
```sh
docker-compose up -d

docker-compose up mercure
```

5️⃣ **Exécuter les migrations si vous utilisez pas mongoDB**
```sh
php bin/console doctrine:migrations:migrate
```

6️⃣ **Démarrer le serveur Symfony**
```sh
symfony server:start
```

---

## 🎯 Utilisation

- Accédez à l'application via **`http://localhost:8000`**
- Inscrivez-vous et attendez la validation de votre identité ✅
- Complétez le **questionnaire Inou** 📝
- Explorez vos matchs et commencez à discuter en temps réel ! 💬

---

## 🤝 Contribution

Les contributions sont **les bienvenues** ! 🎉 N'hésitez pas à soumettre une **pull request** avec vos améliorations.

---

## 📜 Licence

📝 **YNOV License** - Godwin Oblasse, Emmanuel Yohore!

