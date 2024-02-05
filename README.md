# Aaxis Test Backend + API

### Tecnologias

- Symfony 5.3
- PHP 8.0.2


### Levantar por linea de comandos

php -S 127.0.0.1:8010 -t public


### Comandos

rm -Rf var/cache/*

php bin/console c:c

php bin/console list make

php bin/console make:entity --help



## CÓMO GENERAR ENTIDADES A PARTIR DE UNA BASE DE DATOS EXISTENTE

    1. Genera las entidades:
	    php bin/console doctrine:mapping:import "App\Entity" annotation --path=src/Entity
      php bin/console doctrine:mapping:import "App\Entity\SIFAM" annotation --path=src/Entity/db
      php bin/console doctrine:mapping:import "App\Entity\SEOP" annotation --path=src/Entity/db --em db
    
    2. Genera los getters y setters
        php bin/console make:entity --regenerate --overwrite

    3. Importar las tablas de la base como xml
    php bin/console doctrine:mapping:import --help
      1. php bin/console doctrine:mapping:import App\Entity xml --path=config/orm/mapping
      2. php bin/console doctrine:mapping:import App\Entity annotation --path=src/Entity

    4. Validar el esquema
    php bin/console doctrine:schema:validate

