#!/bin/bash

# =============================================================================
# LinkStack Deployment Script for Beget VPS
# =============================================================================
# Этот скрипт автоматизирует процесс развертывания на Beget
# Использование: ./deploy.sh
# =============================================================================

set -e  # Останавливаться при ошибках

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Функция для вывода сообщений
print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_step() {
    echo -e "\n${BLUE}==>${NC} $1\n"
}

# Проверка что мы на сервере Beget
check_environment() {
    print_step "Проверка окружения..."
    
    if [ ! -f "artisan" ]; then
        print_error "Файл artisan не найден. Убедитесь что вы в корне проекта LinkStack."
        exit 1
    fi
    
    print_message "Проект LinkStack найден ✓"
}

# Проверка версии PHP
check_php_version() {
    print_step "Проверка версии PHP..."
    
    PHP_VERSION=$(php -r 'echo PHP_VERSION;')
    PHP_MAJOR=$(php -r 'echo PHP_MAJOR_VERSION;')
    PHP_MINOR=$(php -r 'echo PHP_MINOR_VERSION;')
    
    print_message "Текущая версия PHP: $PHP_VERSION"
    
    if [ "$PHP_MAJOR" -lt 8 ] || ([ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -lt 1 ]); then
        print_error "Требуется PHP 8.1 или выше. Текущая версия: $PHP_VERSION"
        print_warning "В Beget можно выбрать версию PHP в панели управления или использовать алиас:"
        echo "alias php='/usr/bin/php82'"
        exit 1
    fi
    
    print_message "Версия PHP подходит ✓"
}

# Проверка наличия .env файла
check_env_file() {
    print_step "Проверка файла .env..."
    
    if [ ! -f ".env" ]; then
        print_warning "Файл .env не найден. Копирую из .env.example..."
        
        if [ -f ".env.example" ]; then
            cp .env.example .env
            print_message "Файл .env создан из .env.example"
            print_warning "ВАЖНО: Отредактируйте .env файл и добавьте данные базы данных и домен!"
            echo ""
            echo "Необходимо настроить:"
            echo "  - APP_URL (ваш домен)"
            echo "  - DB_DATABASE (имя базы данных)"
            echo "  - DB_USERNAME (логин базы данных)"
            echo "  - DB_PASSWORD (пароль базы данных)"
            echo ""
            read -p "Нажмите Enter когда отредактируете .env файл..."
        else
            print_error "Файл .env.example не найден!"
            exit 1
        fi
    else
        print_message "Файл .env найден ✓"
    fi
}

# Установка зависимостей Composer
install_composer_dependencies() {
    print_step "Установка зависимостей Composer..."
    
    if [ ! -f "composer.phar" ] && ! command -v composer &> /dev/null; then
        print_warning "Composer не найден. Устанавливаю локально..."
        curl -sS https://getcomposer.org/installer | php
        chmod +x composer.phar
        COMPOSER_CMD="./composer.phar"
    elif [ -f "composer.phar" ]; then
        COMPOSER_CMD="./composer.phar"
    else
        COMPOSER_CMD="composer"
    fi
    
    print_message "Используется: $COMPOSER_CMD"
    
    $COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction
    
    print_message "Зависимости установлены ✓"
}

# Генерация ключа приложения
generate_app_key() {
    print_step "Генерация ключа приложения..."
    
    # Проверяем есть ли уже ключ
    if grep -q "APP_KEY=base64:" .env; then
        print_warning "APP_KEY уже установлен. Пропускаю..."
    else
        php artisan key:generate --force
        print_message "APP_KEY сгенерирован ✓"
    fi
}

# Настройка прав доступа
set_permissions() {
    print_step "Настройка прав доступа..."
    
    chmod -R 775 storage
    chmod -R 775 bootstrap/cache
    
    # Создаем необходимые директории
    mkdir -p storage/framework/cache/data
    mkdir -p storage/framework/sessions
    mkdir -p storage/framework/views
    mkdir -p storage/logs
    mkdir -p storage/app/public
    
    chmod -R 775 storage/framework
    chmod -R 775 storage/logs
    
    print_message "Права доступа установлены ✓"
}

# Создание символической ссылки для storage
create_storage_link() {
    print_step "Создание символической ссылки для storage..."
    
    if [ -L "public/storage" ]; then
        print_warning "Ссылка public/storage уже существует. Пропускаю..."
    else
        php artisan storage:link
        print_message "Символическая ссылка создана ✓"
    fi
}

# Миграция базы данных
migrate_database() {
    print_step "Миграция базы данных..."
    
    read -p "Выполнить миграцию базы данных? (y/n): " -n 1 -r
    echo
    
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan migrate --force
        print_message "Миграция выполнена ✓"
        
        read -p "Заполнить базу начальными данными (seeds)? (y/n): " -n 1 -r
        echo
        
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            php artisan db:seed --force --class=AdminSeeder
            php artisan db:seed --force --class=PageSeeder
            php artisan db:seed --force --class=ButtonSeeder
            print_message "Начальные данные загружены ✓"
        fi
    else
        print_warning "Миграция пропущена"
    fi
}

# Оптимизация для production
optimize_for_production() {
    print_step "Оптимизация для production..."
    
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan optimize
    
    print_message "Оптимизация завершена ✓"
}

# Проверка конфигурации
verify_configuration() {
    print_step "Проверка конфигурации..."
    
    # Проверяем критичные настройки в .env
    if ! grep -q "APP_ENV=production" .env; then
        print_warning "APP_ENV не установлен в 'production'"
    fi
    
    if ! grep -q "APP_DEBUG=false" .env; then
        print_warning "APP_DEBUG не установлен в 'false'"
    fi
    
    if ! grep -q "ALLOW_CUSTOM_CODE_IN_THEMES=true" .env; then
        print_warning "ALLOW_CUSTOM_CODE_IN_THEMES не установлен в 'true' (нужно для Cloudy-Storm)"
    fi
    
    if ! grep -q "ALLOW_USER_HTML=true" .env; then
        print_warning "ALLOW_USER_HTML не установлен в 'true' (нужно для Quill editor)"
    fi
    
    print_message "Проверка конфигурации завершена"
}

# Создание бэкапа (если это обновление)
create_backup() {
    if [ -d "storage/app" ] && [ "$(ls -A storage/app)" ]; then
        print_step "Создание бэкапа..."
        
        BACKUP_DIR="../backups"
        mkdir -p "$BACKUP_DIR"
        
        BACKUP_NAME="linkstack-backup-$(date +%Y%m%d-%H%M%S).tar.gz"
        
        tar -czf "$BACKUP_DIR/$BACKUP_NAME" \
            storage/app \
            .env \
            --exclude='storage/framework/cache/*' \
            --exclude='storage/framework/sessions/*' \
            --exclude='storage/framework/views/*' \
            --exclude='storage/logs/*'
        
        print_message "Бэкап создан: $BACKUP_DIR/$BACKUP_NAME ✓"
    fi
}

# Финальная информация
print_final_info() {
    print_step "Развертывание завершено!"
    
    echo ""
    echo -e "${GREEN}═══════════════════════════════════════════════════════${NC}"
    echo -e "${GREEN}  LinkStack успешно развернут!${NC}"
    echo -e "${GREEN}═══════════════════════════════════════════════════════${NC}"
    echo ""
    echo "Следующие шаги:"
    echo ""
    echo "1. Откройте ваш сайт в браузере"
    echo "2. Зарегистрируйте первого пользователя (станет администратором)"
    echo "3. Войдите в систему и перейдите в Studio → Appearance"
    echo "4. Выберите тему Cloudy-Storm или PolySleek"
    echo "5. Настройте профиль и добавьте ссылки"
    echo ""
    echo "Полезные команды:"
    echo ""
    echo "  Очистить кэш:"
    echo "    php artisan cache:clear"
    echo "    php artisan config:clear"
    echo "    php artisan route:clear"
    echo "    php artisan view:clear"
    echo ""
    echo "  Посмотреть логи:"
    echo "    tail -f storage/logs/laravel.log"
    echo ""
    echo "  Создать бэкап базы данных:"
    echo "    mysqldump -h localhost -u USERNAME -p DATABASE > backup.sql"
    echo ""
    echo -e "${YELLOW}Важно:${NC} Убедитесь что корневая директория сайта указывает на /public/"
    echo ""
    echo "Документация: claudedocs/beget-deployment-guide.md"
    echo ""
}

# =============================================================================
# Главная функция
# =============================================================================

main() {
    echo ""
    echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}  LinkStack Deployment Script${NC}"
    echo -e "${BLUE}  Автоматизированное развертывание на Beget VPS${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"
    echo ""
    
    # Проверка что скрипт запущен из корня проекта
    check_environment
    
    # Проверка версии PHP
    check_php_version
    
    # Создание бэкапа (если это обновление)
    create_backup
    
    # Проверка .env файла
    check_env_file
    
    # Установка зависимостей
    install_composer_dependencies
    
    # Генерация ключа
    generate_app_key
    
    # Настройка прав
    set_permissions
    
    # Создание символической ссылки
    create_storage_link
    
    # Миграция базы данных
    migrate_database
    
    # Оптимизация
    optimize_for_production
    
    # Проверка конфигурации
    verify_configuration
    
    # Финальная информация
    print_final_info
}

# Запуск скрипта
main
