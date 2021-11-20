### Hexlet tests and linter status:
[![Maintainability](https://api.codeclimate.com/v1/badges/b66ab65ee8563ca49d1a/maintainability)](https://codeclimate.com/github/GoldinEA/php-project-lvl2/maintainability)
[![Actions Status](https://github.com/GoldinEA/php-project-lvl2/workflows/hexlet-check/badge.svg)](https://github.com/GoldinEA/php-project-lvl2/actions)
[![Test Coverage](https://api.codeclimate.com/v1/badges/b66ab65ee8563ca49d1a/test_coverage)](https://codeclimate.com/github/GoldinEA/php-project-lvl2/test_coverage)
## Описание проекта:

Вычислитель отличий – программа, определяющая разницу между двумя структурами данных. Это популярная задача, для решения которой существует множество онлайн сервисов, например http://www.jsondiff.com/. Подобный механизм используется при выводе тестов или при автоматическом отслеживании изменении в конфигурационных файлах.

Возможности утилиты:

Поддержка разных входных форматов: yaml, json
Генерация отчета в виде plain text, stylish и json

## Установка.
git clone https://github.com/GoldinEA/php-project-lvl2.git

composer install

### Использование.
    gendiff -h
    
    Generate diff
    
    Usage:
    gendiff (-h|--help)
    gendiff (-v|--version)
    gendiff [--format <fmt>] <firstFile> <secondFile>
    
    Options:
    -h --help                     Show this screen
    -v --version                  Show version
    --format <fmt>                Report format [default: stylish]

### Примеры.

#### Сравнение плоских файлов (yaml)
[![asciicast](https://asciinema.org/a/eWLeqfrKg9uhWTLyMB7iodhyS.svg)](https://asciinema.org/a/eWLeqfrKg9uhWTLyMB7iodhyS)
#### Сравнение плоских файлов (json)
[![asciicast](https://asciinema.org/a/XwKMPxXDkeYkQGkGzNeQmLCDA.svg)](https://asciinema.org/a/XwKMPxXDkeYkQGkGzNeQmLCDA)
#### Рекурсивное сравнение
[![asciicast](https://asciinema.org/a/hUkt9LsabcnucRzhmk6H0cIS9.svg)](https://asciinema.org/a/hUkt9LsabcnucRzhmk6H0cIS9)
