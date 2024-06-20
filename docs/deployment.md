# Guide de Déploiement de l'Application Symfony avec Docker

Ce guide explique comment déployer notre application Symfony en utilisant Docker sur une machine Ubuntu.

## Étapes de Déploiement

### 1. Installer Docker

Si Docker n'est pas déjà installer sur la machine Ubuntu, suivre ces étapes pour l'installer :

```bash
# Add Docker's official GPG key:
sudo apt-get update
sudo apt-get install ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc

# Add the repository to Apt sources:
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update

sudo apt-get install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

Vérifier que Docker est bien installé en exécutant :

```bash
docker --version
```

### 2. Cloner le Projet Symfony

Cloner le projet ou le mettre à jour avec le repository Git.

```bash
git clone <url_du_repository>
cd <nom_du_projet>
```

### 3. Déploiement

Pour déployer l'application :

```bash
sudo docker-compose -f docker-compose.yaml -f docker-compose.prod.yaml up -d --build --remove-orphans --force-recreate
```

Cette commande construit les images Docker, lance les conteneurs en arrière-plan (`-d`), et assure que les conteneurs sont recréés si nécessaire (`--force-recreate`).

### 4. Installation des Dépendances PHP

Pour installer les dépendances PHP, exécutez la commande suivante à partir du conteneur PHP :

```bash
sudo docker exec -it <nom_de_votre_projet>_php_1 composer install --optimize-autoloader --no-dev
```

Remplacer `<nom_de_votre_projet>_php_1` par le nom réel du conteneur PHP, qui peut être différent.

### 5. Installation des Dépendances JavaScript

Pour les dépendances JavaScript, exécuter ces commandes depuis la racine du projet :

```bash
# À exécuter depuis la racine du projet
npm install

# Si nécessaire pour la compilation (par exemple, pour les environnements de développement)
npm run watch
```

### 6. Accéder à l'Application

L'application devrait maintenant être accessible à l'adresse IP du serveur Ubuntu.
