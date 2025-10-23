# Быстрые команды для управления LinkStack на Beget

## Подключение к серверу

```bash
# SSH подключение
ssh ваш_логин@ваш_логин.beget.tech

# Переход в директорию проекта
cd ~/example.com  # Замените на ваш домен
```

---

## Очистка кэша

Используйте когда:
- Изменили .env файл
- Обновили конфигурацию
- Что-то работает неправильно

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**Одной командой:**
```bash
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear
```

---

## Пересборка кэша (Production)

После очистки кэша для лучшей производительности:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

**Одной командой:**
```bash
php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan optimize
```

---

## Просмотр логов

### Последние 50 строк
```bash
tail -n 50 storage/logs/laravel.log
```

### Мониторинг в реальном времени
```bash
tail -f storage/logs/laravel.log
```

### Поиск ошибок
```bash
grep "ERROR" storage/logs/laravel.log
grep "CRITICAL" storage/logs/laravel.log
```

### Очистка логов
```bash
> storage/logs/laravel.log
```

---

## Обновление проекта

### Через Git (рекомендуется)

```bash
# Режим обслуживания
php artisan down

# Получение обновлений
git pull origin main

# Обновление зависимостей
composer install --no-dev --optimize-autoloader

# Миграции базы данных
php artisan migrate --force

# Очистка и пересборка кэша
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Включение сайта
php artisan up
```

### Полная команда обновления
```bash
php artisan down && \
git pull origin main && \
composer install --no-dev --optimize-autoloader && \
php artisan migrate --force && \
php artisan cache:clear && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
php artisan up
```

---

## Бэкап

### Бэкап базы данных MySQL

```bash
# Создание бэкапа
mysqldump -h localhost -u ваш_логин_linkstack_db -p ваш_логин_linkstack_db > backup-$(date +%Y%m%d).sql

# С автоматическим именем файла
mysqldump -h localhost -u ваш_логин_linkstack_db -p ваш_логин_linkstack_db > backup-$(date +%Y%m%d-%H%M%S).sql
```

### Бэкап файлов проекта

```bash
# Полный бэкап
cd ~/
tar -czf linkstack-backup-$(date +%Y%m%d).tar.gz example.com/

# Только важные файлы (storage, .env, база SQLite если используется)
cd ~/example.com
tar -czf ../linkstack-important-$(date +%Y%m%d).tar.gz \
  storage/app \
  .env \
  database/database.sqlite
```

### Восстановление из бэкапа

```bash
# База данных
mysql -h localhost -u ваш_логин_linkstack_db -p ваш_логин_linkstack_db < backup-20251023.sql

# Файлы
cd ~/
tar -xzf linkstack-backup-20251023.tar.gz
```

---

## Проверка статуса

### Проверка PHP версии
```bash
php -v
```

### Проверка Composer
```bash
composer --version
```

### Проверка прав доступа
```bash
ls -la storage/
ls -la bootstrap/cache/
```

### Проверка размера директорий
```bash
du -sh storage/
du -sh vendor/
du -sh public/
```

### Проверка подключения к базе данных
```bash
php artisan tinker --execute="DB::connection()->getPdo();"
```

---

## Управление режимом обслуживания

### Включить режим обслуживания
```bash
php artisan down
```

### С кастомным сообщением
```bash
php artisan down --message="Обновление системы. Вернемся через 10 минут" --retry=600
```

### Отключить режим обслуживания
```bash
php artisan up
```

---

## Пересоздание символической ссылки storage

Если загруженные файлы не отображаются:

```bash
# Удалить старую ссылку
rm public/storage

# Создать новую
php artisan storage:link
```

---

## Права доступа (если возникли проблемы)

```bash
# Установить правильные права
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod -R 775 storage/framework
chmod -R 775 storage/logs

# Проверить владельца (замените username на ваш логин)
chown -R username:username storage
chown -R username:username bootstrap/cache
```

---

## Миграции базы данных

### Выполнить новые миграции
```bash
php artisan migrate --force
```

### Откатить последнюю миграцию
```bash
php artisan migrate:rollback --step=1
```

### Пересоздать всю базу данных (ОСТОРОЖНО!)
```bash
php artisan migrate:fresh --force
```

### Пересоздать с seed'ами
```bash
php artisan migrate:fresh --seed --force
```

---

## Работа с очередями (если используются)

### Запуск worker'а
```bash
php artisan queue:work
```

### Проверка failed jobs
```bash
php artisan queue:failed
```

### Повтор failed job
```bash
php artisan queue:retry all
```

---

## Отладка

### Проверка конфигурации
```bash
php artisan config:show
```

### Список маршрутов
```bash
php artisan route:list
```

### Информация о приложении
```bash
php artisan about
```

### Проверка переменных окружения
```bash
php artisan tinker --execute="echo env('APP_URL');"
php artisan tinker --execute="echo config('app.url');"
```

### Тестирование email (если настроен)
```bash
php artisan tinker
# Затем в tinker:
Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

---

## Очистка старых файлов

### Удаление старых логов (старше 7 дней)
```bash
find storage/logs/ -name "*.log" -type f -mtime +7 -delete
```

### Очистка кэша
```bash
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*
```

---

## Git команды (если используется Git)

### Проверка статуса
```bash
git status
```

### Получение изменений
```bash
git pull origin main
```

### Откат к определенному коммиту
```bash
git log  # Найти hash коммита
git reset --hard <commit-hash>
```

### Создание ветки для тестирования
```bash
git checkout -b test-branch
```

### Возврат к main
```bash
git checkout main
```

---

## Composer команды

### Обновление одного пакета
```bash
composer update vendor/package --no-dev
```

### Обновление всех пакетов
```bash
composer update --no-dev
```

### Проверка устаревших пакетов
```bash
composer outdated
```

### Очистка кэша Composer
```bash
composer clear-cache
```

---

## Мониторинг производительности

### Размер базы данных
```bash
mysql -h localhost -u ваш_логин_linkstack_db -p -e "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.TABLES GROUP BY table_schema;"
```

### Топ 10 самых больших файлов
```bash
find . -type f -exec du -h {} + | sort -rh | head -10
```

### Использование диска
```bash
df -h
```

### Память
```bash
free -m
```

---

## Автоматизация через cron

Добавьте в панели Beget → Планировщик задач (Cron):

```bash
# Каждую минуту (для Laravel scheduler)
* * * * * /usr/bin/php82 /home/ваш_логин/example.com/artisan schedule:run >> /dev/null 2>&1

# Ежедневный бэкап в 3:00 ночи
0 3 * * * mysqldump -h localhost -u ваш_логин_linkstack_db -p'пароль' ваш_логин_linkstack_db > /home/ваш_логин/backups/db-$(date +\%Y\%m\%d).sql

# Очистка старых логов каждую неделю
0 2 * * 0 find /home/ваш_логин/example.com/storage/logs/ -name "*.log" -type f -mtime +30 -delete
```

---

## Полезные алиасы

Добавьте в `~/.bashrc` для удобства:

```bash
# Добавить в ~/.bashrc
echo "alias artisan='php artisan'" >> ~/.bashrc
echo "alias clearall='php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear'" >> ~/.bashrc
echo "alias cacheall='php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan optimize'" >> ~/.bashrc
echo "alias logs='tail -f storage/logs/laravel.log'" >> ~/.bashrc

# Применить изменения
source ~/.bashrc
```

Теперь можно использовать:
```bash
artisan cache:clear
clearall
cacheall
logs
```

---

## Экстренное восстановление

Если сайт полностью сломался:

```bash
# 1. Режим обслуживания
php artisan down

# 2. Откат Git (если используется)
git reset --hard HEAD~1

# 3. Полная очистка кэша
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*
rm -rf bootstrap/cache/*

# 4. Пересоздание кэша
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Включить сайт
php artisan up

# 6. Проверить логи
tail -n 100 storage/logs/laravel.log
```

---

**Совет:** Сохраните эти команды в текстовом файле на вашем компьютере для быстрого доступа!
