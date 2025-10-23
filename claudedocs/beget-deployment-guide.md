# Инструкция по развертыванию LinkStack на VPS Beget

## Содержание
1. [Подготовка проекта](#1-подготовка-проекта)
2. [Настройка Beget](#2-настройка-beget)
3. [Загрузка файлов](#3-загрузка-файлов)
4. [Настройка окружения](#4-настройка-окружения)
5. [Установка зависимостей](#5-установка-зависимостей)
6. [Настройка базы данных](#6-настройка-базы-данных)
7. [Настройка веб-сервера](#7-настройка-веб-сервера)
8. [Финальная проверка](#8-финальная-проверка)

---

## 1. Подготовка проекта

### 1.1. Проверка текущей конфигурации

На вашем локальном компьютере проверьте, что все работает:

```bash
cd /Users/max/Documents/CloudeCode/LinkStack/LinkStack
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 1.2. Создание архива проекта

**Вариант А: Через Git (рекомендуется)**
```bash
# Убедитесь, что все закоммичено
git status
git add .
git commit -m "Prepare for Beget deployment"
git push origin main
```

**Вариант Б: Через архив**
```bash
# Создайте архив без node_modules и vendor
cd /Users/max/Documents/CloudeCode/LinkStack
tar -czf linkstack-deploy.tar.gz \
  --exclude='LinkStack/node_modules' \
  --exclude='LinkStack/vendor' \
  --exclude='LinkStack/.git' \
  --exclude='LinkStack/storage/logs/*' \
  --exclude='LinkStack/storage/framework/cache/*' \
  --exclude='LinkStack/storage/framework/sessions/*' \
  --exclude='LinkStack/storage/framework/views/*' \
  LinkStack/
```

---

## 2. Настройка Beget

### 2.1. Войдите в панель управления Beget

1. Откройте https://cp.beget.com/
2. Войдите в свой аккаунт

### 2.2. Создайте базу данных MySQL

1. Перейдите в раздел **"MySQL" → "Базы данных"**
2. Нажмите **"Добавить базу данных"**
3. Заполните:
   - **Имя базы данных**: `linkstack_db`
   - **Кодировка**: `utf8mb4_unicode_ci`
4. Нажмите **"Создать"**
5. **Запишите данные:**
   - Имя базы: `<ваш_логин>_linkstack_db`
   - Хост: `localhost`
   - Логин: `<ваш_логин>_linkstack_db`
   - Пароль: `<сгенерированный_пароль>`

### 2.3. Настройте домен

1. Перейдите в **"Сайты" → "Список сайтов"**
2. Если домен не добавлен:
   - Нажмите **"Добавить сайт"**
   - Введите домен (например, `example.com`)
   - Корневая директория: `/home/<ваш_логин>/example.com`
3. Важно: убедитесь что включен **PHP 8.1 или 8.2**

### 2.4. Получите SSH доступ

1. Перейдите в **"SSH/SFTP" → "Доступ по SSH"**
2. Если SSH не активирован, нажмите **"Включить"**
3. **Запишите данные для подключения:**
   - Хост: `<ваш_домен>.beget.tech` или `<ваш_логин>.beget.tech`
   - Порт: `22`
   - Логин: `<ваш_логин>`
   - Пароль: `<ваш_пароль>`

---

## 3. Загрузка файлов

### 3.1. Подключитесь по SSH

```bash
ssh <ваш_логин>@<ваш_логин>.beget.tech
```

### 3.2. Перейдите в директорию сайта

```bash
cd ~/example.com  # Замените на ваш домен
```

### 3.3. Загрузите проект

**Вариант А: Через Git (рекомендуется)**
```bash
# Клонируйте репозиторий
git clone https://github.com/ваш_аккаунт/linkstack.git .

# Или если репозиторий приватный
git clone https://ваш_токен@github.com/ваш_аккаунт/linkstack.git .
```

**Вариант Б: Через SFTP**
1. Используйте FileZilla или другой SFTP клиент
2. Подключитесь:
   - Хост: `<ваш_логин>.beget.tech`
   - Порт: `22`
   - Протокол: SFTP
   - Логин/пароль: те же что для SSH
3. Загрузите все файлы в `~/example.com/`

**Вариант В: Через SCP (с вашего Mac)**
```bash
# Загрузите архив
scp linkstack-deploy.tar.gz <ваш_логин>@<ваш_логин>.beget.tech:~/example.com/

# Подключитесь по SSH и распакуйте
ssh <ваш_логин>@<ваш_логин>.beget.tech
cd ~/example.com
tar -xzf linkstack-deploy.tar.gz --strip-components=1
rm linkstack-deploy.tar.gz
```

---

## 4. Настройка окружения

### 4.1. Создайте файл .env

```bash
cd ~/example.com
cp .env.example .env
nano .env  # или используйте vi
```

### 4.2. Настройте .env файл

Замените следующие параметры:

```env
APP_NAME=LinkStack
APP_ENV=production
APP_KEY=  # Сгенерируем позже
APP_DEBUG=false
APP_URL=https://example.com  # Ваш домен

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=<ваш_логин>_linkstack_db
DB_USERNAME=<ваш_логин>_linkstack_db
DB_PASSWORD=<пароль_базы_данных>

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REGISTER_AUTH=auth

ALLOW_CUSTOM_CODE_IN_THEMES=true
ALLOW_USER_HTML=true
```

**Сохраните файл:**
- Nano: `Ctrl+O`, `Enter`, `Ctrl+X`
- Vi: `Esc`, `:wq`, `Enter`

---

## 5. Установка зависимостей

### 5.1. Проверьте версию PHP

```bash
php -v
# Должно быть PHP 8.1 или 8.2
```

Если версия старая, переключитесь:
```bash
# В Beget обычно можно выбрать версию PHP через панель управления
# Или использовать алиас
alias php='/usr/bin/php82'  # Или php81
```

### 5.2. Установите Composer (если не установлен)

```bash
# Проверьте наличие Composer
composer --version

# Если не установлен, установите локально
cd ~/example.com
curl -sS https://getcomposer.org/installer | php
mv composer.phar composer
chmod +x composer
```

### 5.3. Установите зависимости PHP

```bash
cd ~/example.com

# Если Composer установлен глобально
composer install --no-dev --optimize-autoloader

# Если используете локальный composer
./composer install --no-dev --optimize-autoloader
```

### 5.4. Установите зависимости Node.js (опционально)

Если на Beget нет Node.js, можно собрать assets локально и загрузить:

**На вашем Mac:**
```bash
cd /Users/max/Documents/CloudeCode/LinkStack/LinkStack
npm install
npm run production
```

Затем загрузите папку `public/` на сервер через SFTP.

---

## 6. Настройка базы данных

### 6.1. Сгенерируйте ключ приложения

```bash
cd ~/example.com
php artisan key:generate
```

### 6.2. Создайте файл базы данных SQLite (опционально)

Если используете SQLite вместо MySQL:
```bash
touch database/database.sqlite
chmod 664 database/database.sqlite
```

И в .env:
```env
DB_CONNECTION=sqlite
# Закомментируйте остальные DB_ параметры
```

### 6.3. Выполните миграции

```bash
php artisan migrate --force
```

### 6.4. Создайте начальные данные (seed)

```bash
php artisan db:seed --force --class=AdminSeeder
php artisan db:seed --force --class=PageSeeder
php artisan db:seed --force --class=ButtonSeeder
```

---

## 7. Настройка веб-сервера

### 7.1. Настройка корневой директории

**ВАЖНО:** Корневая директория должна указывать на `public/`

**В панели Beget:**
1. Перейдите в **"Сайты" → "Список сайтов"**
2. Нажмите на шестеренку рядом с вашим доменом
3. Найдите **"Корневая директория"**
4. Измените на: `/home/<ваш_логин>/example.com/public`
5. Сохраните

**Альтернатива через .htaccess (если нельзя изменить корень):**

Создайте `.htaccess` в корне сайта:
```bash
cd ~/example.com
nano .htaccess
```

Добавьте:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### 7.2. Настройте права доступа

```bash
cd ~/example.com

# Папки для записи
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Владелец (замените username на ваш логин)
chown -R username:username storage
chown -R username:username bootstrap/cache

# Создайте необходимые директории если их нет
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/public

chmod -R 775 storage/framework
chmod -R 775 storage/logs
```

### 7.3. Создайте символическую ссылку для storage

```bash
php artisan storage:link
```

### 7.4. Оптимизация для production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## 8. Финальная проверка

### 8.1. Проверьте, что все файлы на месте

```bash
cd ~/example.com
ls -la

# Должны быть:
# - app/
# - bootstrap/
# - config/
# - database/
# - public/
# - resources/
# - routes/
# - storage/
# - themes/
# - vendor/
# - .env
# - artisan
```

### 8.2. Проверьте права доступа

```bash
ls -la storage/
ls -la bootstrap/cache/
# Все должны быть writable (775 или 755)
```

### 8.3. Откройте сайт в браузере

1. Перейдите на `https://example.com` (ваш домен)
2. Должна открыться страница регистрации/входа
3. Зарегистрируйте первого пользователя (будет админом)

### 8.4. Войдите в админ-панель

1. После регистрации войдите в систему
2. Перейдите в **Studio → Appearance**
3. Выберите тему **Cloudy-Storm** или **PolySleek**
4. Настройте профиль

### 8.5. Проверьте функционал

- ✅ Загрузка аватара
- ✅ Изменение описания с форматированием (Quill editor)
- ✅ Создание ссылок
- ✅ Кнопка "Поделиться"
- ✅ Анимация молний (для Cloudy-Storm)
- ✅ Красное выделение текста

---

## Устранение проблем

### Проблема: "500 Internal Server Error"

1. Проверьте логи:
```bash
tail -n 50 storage/logs/laravel.log
```

2. Проверьте права:
```bash
chmod -R 775 storage bootstrap/cache
```

3. Очистите кэш:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Проблема: "No application encryption key"

```bash
php artisan key:generate
php artisan config:cache
```

### Проблема: База данных не подключается

1. Проверьте данные в .env
2. Проверьте что база создана в панели Beget
3. Попробуйте подключиться вручную:
```bash
mysql -h localhost -u <ваш_логин>_linkstack_db -p
# Введите пароль
```

### Проблема: Стили/JS не загружаются

1. Проверьте APP_URL в .env
2. Запустите:
```bash
php artisan storage:link
npm run production  # На локальной машине, затем загрузите public/
```

### Проблема: Composer не устанавливается

```bash
# Используйте --no-scripts если есть ошибки
composer install --no-dev --no-scripts --optimize-autoloader

# Затем запустите скрипты отдельно
php artisan key:generate
```

---

## Обновление проекта

### Через Git (рекомендуется)

```bash
cd ~/example.com

# Сохраните изменения
php artisan down  # Режим обслуживания

# Получите обновления
git pull origin main

# Обновите зависимости
composer install --no-dev --optimize-autoloader

# Выполните миграции
php artisan migrate --force

# Очистите и пересоберите кэш
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Включите сайт
php artisan up
```

### Через архив

1. Сделайте бэкап:
```bash
cd ~/
tar -czf linkstack-backup-$(date +%Y%m%d).tar.gz example.com/
```

2. Загрузите новые файлы через SFTP
3. Выполните команды обновления из раздела выше

---

## Бэкап

### Бэкап базы данных

```bash
# Через mysqldump
mysqldump -h localhost -u <ваш_логин>_linkstack_db -p <ваш_логин>_linkstack_db > backup-$(date +%Y%m%d).sql

# Скачайте бэкап на локальный компьютер
scp <ваш_логин>@<ваш_логин>.beget.tech:~/backup-$(date +%Y%m%d).sql ~/Downloads/
```

### Бэкап файлов

```bash
cd ~/
tar -czf linkstack-files-backup-$(date +%Y%m%d).tar.gz \
  example.com/storage/app \
  example.com/.env \
  example.com/database/database.sqlite
```

---

## Настройка HTTPS (SSL)

В Beget SSL обычно настраивается автоматически через Let's Encrypt:

1. Перейдите в **"Домены" → "SSL-сертификаты"**
2. Выберите ваш домен
3. Нажмите **"Получить SSL-сертификат"**
4. Выберите **"Let's Encrypt"**
5. Включите **"Автопродление"**

После установки SSL обновите .env:
```env
APP_URL=https://example.com
```

И очистите кэш:
```bash
php artisan config:cache
```

---

## Автоматизация (Cron задачи)

Если нужны периодические задачи:

1. В панели Beget перейдите в **"Планировщик задач (Cron)"**
2. Добавьте задачу:
   - Команда: `/usr/bin/php82 /home/<ваш_логин>/example.com/artisan schedule:run`
   - Расписание: `* * * * *` (каждую минуту)

---

## Контакты для поддержки

- **Документация LinkStack**: https://linkstack.org/docs
- **Техподдержка Beget**: https://beget.com/support
- **GitHub Issues**: https://github.com/LinkStackOrg/LinkStack/issues

---

**Автор**: Claude Code  
**Дата создания**: 2025-10-23  
**Версия**: 1.0
