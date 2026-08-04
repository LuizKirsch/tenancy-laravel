# Tenancy Laravel

Aplicação Laravel multi-tenant com **banco de dados separado por tenant**, usando
[`stancl/tenancy`](https://tenancyforlaravel.com) com identificação por domínio/subdomínio.

Cada tenant cadastrado ganha automaticamente:
- um registro em `tenants` (id UUID) e um em `domains`;
- um banco de dados próprio (`tenancy_<uuid>_database`);
- as migrations de `database/migrations/tenant/` rodadas nesse banco.

---

## Stack

| Componente | Versão em uso |
|---|---|
| PHP | 8.1 |
| Laravel | 10.x |
| stancl/tenancy | 3.8 |
| Livewire | 3.x |
| Sanctum | 3.x |
| MySQL | 8.x |
| Vite | 4.x |
| Servidor | Apache 2.4 (Ubuntu) |

> **Nota:** este stack está desatualizado (PHP 8.1 e Laravel 10 já saíram do suporte de
> segurança). Há um plano de atualização em andamento — veja [Roadmap](#roadmap).

---

## Estrutura relevante

```
app/Models/Tenant.php                  Model do tenant (HasDatabase, HasDomains)
app/Providers/TenancyServiceProvider   Jobs disparados ao criar/deletar tenant
config/tenancy.php                     central_domains, prefix/suffix do banco
routes/web.php                         Rotas do painel central (admin)
routes/tenant.php                      Rotas servidas dentro de cada tenant
database/migrations/                   Migrations do banco CENTRAL
database/migrations/tenant/            Migrations replicadas em CADA tenant
```

**Domínios centrais** estão em `config/tenancy.php` → `central_domains`. Hoje:
`127.0.0.1` e `localhost`. Qualquer domínio fora dessa lista é tratado como tenant.

---

## Instalação em servidor (Ubuntu + Apache)

### 1. Atualizar o sistema

```bash
sudo apt update
sudo apt upgrade
```

### 2. Apache

```bash
sudo apt install apache2
```

Habilite o `mod_rewrite` — sem ele o Laravel devolve 404 em qualquer rota
que não seja a raiz:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 3. MySQL

```bash
sudo apt install mysql-server
```

### 4. PHP 8.1 (via PPA do ondrej)

```bash
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.1
```

### 5. Extensões do PHP

```bash
sudo apt install php8.1-mysql php8.1-curl php8.1-gd php8.1-intl \
                 php8.1-mbstring php8.1-xml php8.1-zip php8.1-bcmath
```

### 6. Ativar o módulo PHP no Apache

```bash
sudo a2enmod php8.1
sudo systemctl restart apache2
```

### 7. Validar a instalação do PHP

```bash
echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/phpinfo.php
```

Acesse `http://SEU_HOST/phpinfo.php`. Confirme a versão e as extensões.

> **Apague esse arquivo depois.** Ele expõe caminhos, extensões e configuração do
> servidor publicamente:
> ```bash
> sudo rm /var/www/html/phpinfo.php
> ```

### 8. Chave SSH para o GitHub

```bash
ssh-keygen
cat ~/.ssh/id_rsa.pub
```

Copie a saída e cadastre em **GitHub → Settings → SSH and GPG keys → New SSH key**.

### 9. Clonar o projeto

```bash
cd /var/www/html
git clone git@github.com:SEU_USUARIO/tenancy-laravel.git
cd tenancy-laravel
git checkout main
```

### 10. Composer

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

> O hash acima é fixo por versão do instalador. Se aparecer `Installer corrupt`,
> pegue o hash atual em <https://getcomposer.org/download/>.

### 11. Dependências da aplicação

```bash
cd /var/www/html/tenancy-laravel
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

### 12. Permissões

O Apache roda como `www-data` e precisa escrever em `storage/` e `bootstrap/cache/`:

```bash
sudo chown -R www-data:www-data /var/www/html/tenancy-laravel
sudo chmod -R 775 /var/www/html/tenancy-laravel/storage
sudo chmod -R 775 /var/www/html/tenancy-laravel/bootstrap/cache
```

### 13. Banco de dados e usuário MySQL

```bash
sudo mysql
```

```sql
CREATE DATABASE laravel;
CREATE USER 'SEU_USUARIO'@'localhost' IDENTIFIED BY 'SUA_SENHA_FORTE';
GRANT ALL PRIVILEGES ON *.* TO 'SEU_USUARIO'@'localhost';
FLUSH PRIVILEGES;
```

> **Por que `ON *.*` e não só `ON laravel.*`:** o `stancl/tenancy` cria e derruba um
> banco de dados a cada tenant. O usuário precisa de `CREATE`/`DROP DATABASE` global —
> um GRANT restrito a um schema faz a criação de tenant falhar.
>
> Nunca versione credenciais reais. Use senha forte e mantenha só no `.env` do servidor.

### 14. Configurar o `.env`

```dotenv
APP_NAME="Tenancy Laravel"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://seu-dominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=SEU_USUARIO
DB_PASSWORD=SUA_SENHA_FORTE
```

Em produção, `APP_DEBUG=false` é obrigatório — com `true` o Ignition expõe o
conteúdo do `.env` na tela de erro.

### 15. VirtualHost do Apache

```bash
cd /etc/apache2/sites-available/
sudo nano 000-default.conf
```

```apache
<VirtualHost *:80>
        ServerName seu-dominio.com
        ServerAlias *.seu-dominio.com

        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/html/tenancy-laravel/public

        <Directory /var/www/html/tenancy-laravel/public>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>

# vim: syntax=apache ts=4 sw=4 sts=4 sr noet
```

Dois pontos críticos para multi-tenancy:

- **`ServerAlias *.seu-dominio.com`** — é isso que faz o Apache aceitar os subdomínios
  dos tenants. Sem o curinga, só o domínio central responde.
- **`AllowOverride All`** — permite que o `.htaccess` do Laravel funcione. Sem ele,
  `mod_rewrite` não é aplicado e todas as rotas dão 404.

Também é preciso apontar um **DNS curinga** (`*.seu-dominio.com` → IP do servidor).
Em AWS/KingHost isso é um registro `A` com nome `*`.

### 16. Recarregar o Apache

```bash
sudo a2dissite 000-default.conf
sudo systemctl reload apache2
sudo a2ensite 000-default.conf
sudo systemctl reload apache2
```

### 17. Migrations

```bash
cd /var/www/html/tenancy-laravel

# banco central (tenants, domains, users do admin)
php artisan migrate

# bancos de todos os tenants já existentes
php artisan tenants:migrate
```

### 18. Assets

```bash
sudo apt install nodejs npm
npm install
npm run build
```

---

## Ambiente local (Windows / desenvolvimento)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev          # em outro terminal
php artisan serve
```

O painel central fica em `http://localhost:8000`.

### Acessando um tenant localmente

Os domínios de tenant são criados como `<dominio>.localhost`. No Chrome e no Firefox,
`*.localhost` já resolve para `127.0.0.1` sem configuração. Se o seu ambiente não
resolver, adicione a entrada no hosts:

```
# C:\Windows\System32\drivers\etc\hosts
127.0.0.1   loja-principal.localhost
```

Depois acesse `http://loja-principal.localhost:8000`.

---

## Uso

### Criar um tenant

Pelo painel: **`/login` → `/add-tenancy`**, informe o domínio (ex.: `loja-principal`)
e o plano. Isso dispara, em sequência: `CreateDatabase` → `MigrateDatabase` →
`SeedDatabase`.

Por tinker:

```php
$tenant = App\Models\Tenant::create(['plan' => 'free']);
$tenant->domains()->create(['domain' => 'loja-principal.localhost']);
```

### Comandos de tenancy

```bash
php artisan tenants:list                      # lista os tenants
php artisan tenants:migrate                   # migra todos
php artisan tenants:migrate --tenants=<uuid>  # migra um específico
php artisan tenants:rollback
php artisan tenants:seed
php artisan tenants:run <comando>             # roda um artisan dentro de cada tenant
```

### Migrations de tenant

Migrations que devem existir **em cada tenant** vão em `database/migrations/tenant/`:

```bash
php artisan make:migration create_produtos_table --path=database/migrations/tenant
php artisan tenants:migrate
```

As de `database/migrations/` (sem `tenant/`) rodam **só no banco central**.

---

## Problemas conhecidos

Itens levantados na auditoria e ainda **não corrigidos** — não suba para produção antes
de resolver os dois primeiros:

- **Rotas administrativas sem autenticação.** `/home`, `/add-tenancy` e `/create_action`
  não têm middleware `auth`. Qualquer visitante anônimo pode criar tenants — e cada
  criação gera um banco de dados novo.
- **Erro 500 no login com senha incorreta.** `AuthController::login_action()` chama
  `$user->verifyCredentials()`, método que não existe no model `User`.
- Criação de tenant sem validação (domínio duplicado ou inválido gera exception).
- Sufixo `.localhost` está fixo no código do `AddTenancyController`.
- Contador Livewire tem race condition (`read-modify-write` em vez de `increment()`).
- Tailwind carregado via CDN junto com Bootstrap e Flowbite — conflito de classes e peso.
- Sem cobertura de testes.

---

## Roadmap

Ordem planejada de atualização, um salto por vez:

1. PHP 8.1 → 8.3
2. Laravel 10 → 11 (migrar `app/Http/Kernel.php` e providers para `bootstrap/app.php`)
3. Laravel 11 → 12
4. `stancl/tenancy` 3.8 → 3.9
5. Sanctum 3 → 4, Livewire 3.5 → 3.6, Vite 4 → atual

---

## Licença

MIT.
