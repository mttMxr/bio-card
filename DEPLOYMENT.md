# 🚀 Быстрое развертывание на Beget VPS

## Краткая инструкция (10 минут)

### 1. Подготовка на Beget

**Создайте базу данных MySQL:**
- Войдите в панель: https://cp.beget.com/
- MySQL → Добавить базу данных
- Запишите: имя БД, логин, пароль

**Активируйте SSH:**
- SSH/SFTP → Включить SSH
- Запишите данные доступа

---

### 2. Загрузка файлов

**Вариант A: Git (рекомендуется)**
```bash
ssh ваш_логин@ваш_логин.beget.tech
cd ~/ваш_домен.com
git clone https://github.com/ваш_репозиторий/linkstack.git .
```

**Вариант Б: SFTP**
- Используйте FileZilla
- Загрузите все файлы в `~/ваш_домен.com/`

---

### 3. Автоматическое развертывание

```bash
# Подключитесь по SSH
ssh ваш_логин@ваш_логин.beget.tech
cd ~/ваш_домен.com

# Скопируйте .env
cp .env.example .env
nano .env  # Настройте APP_URL и данные БД

# Запустите скрипт развертывания
./deploy.sh
```

**Скрипт автоматически:**
- ✅ Проверит окружение
- ✅ Установит зависимости
- ✅ Сгенерирует ключ
- ✅ Настроит права
- ✅ Выполнит миграции
- ✅ Оптимизирует кэш

---

### 4. Настройка веб-сервера

**В панели Beget:**
1. Сайты → Ваш домен → Настройки
2. **Корневая директория**: `/home/ваш_логин/ваш_домен.com/public`
3. **PHP версия**: 8.1 или 8.2
4. Сохранить

---

### 5. Настройка SSL

1. Домены → SSL-сертификаты
2. Выбрать домен → Let's Encrypt
3. Включить автопродление

---

### 6. Первый вход

1. Откройте: `https://ваш_домен.com`
2. Зарегистрируйте первого пользователя (станет админом)
3. Войдите → Studio → Appearance
4. Выберите тему: **Cloudy-Storm** или **PolySleek**
5. Настройте профиль

---

## ⚙️ Важные настройки .env

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ваш_домен.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=ваш_логин_linkstack_db
DB_USERNAME=ваш_логин_linkstack_db
DB_PASSWORD=ваш_пароль

ALLOW_CUSTOM_CODE_IN_THEMES=true  # Для анимации молний
ALLOW_USER_HTML=true              # Для Quill editor
REGISTER_AUTH=auth                # Требует авторизацию
```

---

## 🆘 Частые проблемы

### Ошибка 500
```bash
chmod -R 775 storage bootstrap/cache
php artisan cache:clear
tail -n 50 storage/logs/laravel.log
```

### База не подключается
- Проверьте префикс имени БД: `ваш_логин_`
- Проверьте пароль в .env

### Стили не загружаются
```bash
php artisan storage:link
php artisan config:cache
```

---

## 📚 Полная документация

Смотрите в директории **`claudedocs/`**:
- `README.md` - Обзор документации
- `beget-deployment-guide.md` - Подробное руководство
- `deployment-checklist.md` - Чеклист
- `quick-commands.md` - Справка команд
- `project-summary.md` - Сводка проекта

---

## ✅ Проверка работы

После развертывания проверьте:
- [ ] Сайт открывается без ошибок
- [ ] SSL работает (https://)
- [ ] Анимация молний работает (Cloudy-Storm)
- [ ] Rich Text Editor работает
- [ ] Кнопка "Поделиться" работает
- [ ] Загрузка аватара работает

---

## 🔄 Обновление

```bash
ssh ваш_логин@ваш_логин.beget.tech
cd ~/ваш_домен.com

php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan up
```

---

## 💾 Бэкап

```bash
# База данных
mysqldump -h localhost -u ваш_логин_linkstack_db -p ваш_логин_linkstack_db > backup.sql

# Файлы
tar -czf backup.tar.gz storage/app .env
```

---

**Успешного развертывания! 🎉⚡**

Документация создана: 2025-10-23 | Claude Code
