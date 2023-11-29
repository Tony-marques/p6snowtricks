# Développez de A à Z le site communautaire SnowTricks - Symfony - P6 OCR

## Installation :

-   **1.** Cloner le projet
```
git clone https://github.com/Tony-marques/p6snowtricks.git
```

-   **2.** Installer les dépendances back et front `composer install` et `npm install` à la racine du projet

-   **3.** Créer un fichier .env à la racine du projet avec les identifiants de votre base de donnée et de votre serveur SMTP

-   **4.** Créer la base de donnée
```
symfony console doctrine:database:create
```

-   **5.** Créer les tables dans la base de donnée
```
symfony console doctrine:migrations:migrate
```


-   **6.** Créer les données avec les fixtures symfony
```
symfony console doctrine:fixtures:load
```


