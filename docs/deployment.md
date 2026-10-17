# Office deployment

Run the Staff Movement System on one always-on machine in the office, so every PC, phone and the
wall TV on the office network can use it, without anyone's laptop running PowerShell.

The app is packaged with **Docker**. The same setup runs on a Linux server, a Windows PC or a small
mini PC.

```
 Office network (LAN / Wi-Fi)

  Staff PCs, phones ─┐
                     ├──>  http://<server-ip>:8080  ──>  app        (Apache + PHP, the website)
  Wall TV (link) ────┘                                   scheduler  (daily backup at 01:00)
                                                              │
                                                              └── data volume: database,
                                                                  backups, logs, keys
```

Everything that must survive restarts and updates (the database, backups, logs, the app key and the
TV link token) lives in one Docker volume, `attendance-data`.

---

## 1. Pick the machine

Any always-on computer on the office network works: a spare desktop, a mini PC, or a virtual
machine on an existing server.

- 2 CPU cores, 4 GB RAM and 20 GB free disk are plenty for an office of a few hundred staff.
- Give it a **fixed IP address**, so the link people use never changes. The usual way is to ask
  whoever manages the router to reserve an address for the machine (a "DHCP reservation"). The rest
  of this guide uses `192.168.1.50` as the example; replace it with yours.
- It needs internet access while you install and update; day-to-day use is entirely on the LAN.

## 2. Install Docker and Git

### Linux (Ubuntu 22.04 / 24.04)

```sh
sudo apt update && sudo apt install -y git curl
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker $USER     # lets you run docker without sudo; log out and back in
```

Docker starts automatically when the machine boots.

### Windows 10 / 11

1. Install **Git** from https://git-scm.com/download/win.
2. Install **Docker Desktop** from https://www.docker.com/products/docker-desktop/ and accept the
   option to use **WSL 2**. Restart when asked.
3. In Docker Desktop: **Settings → General → Start Docker Desktop when you sign in**.
4. Make Windows sign in automatically after a restart (or keep the machine signed in), otherwise
   Docker Desktop and the app won't start until someone logs in.

> **Licence:** Docker Desktop is free for small organisations (fewer than 250 employees **and**
> less than USD 10 million revenue); larger organisations need a paid subscription. If that applies
> to you, use a Linux machine or virtual machine with Docker Engine (free) instead.

## 3. Get the app

```sh
git clone https://github.com/abdullahzamani/attendance.git
cd attendance
```

On Windows, run these in PowerShell, for example from `C:\` to get `C:\attendance`.

## 4. Configure it

```sh
cp .env.docker.example .env.docker
```

(PowerShell: `Copy-Item .env.docker.example .env.docker`)

Open `.env.docker` in a text editor and change:

| Setting | Set it to |
|---|---|
| `APP_URL` | `http://192.168.1.50:8080`, the address people will type, with **your** server's IP |
| `ADMIN_EMAIL` | the email the first administrator signs in with |
| `ADMIN_PASSWORD` | a strong temporary password; change it after the first login |

Leave `APP_KEY` and `BOARD_TOKEN` empty: they're generated on the first start and stored in the
data volume.

## 5. Start it

```sh
docker compose up -d --build
```

The first build downloads PHP and the app's libraries, so it takes a few minutes. Check it's running:

```sh
docker compose ps
```

Both `app` and `scheduler` should be **Up**, with `app` marked **(healthy)** after about a minute.

On the server itself, open http://localhost:8080 and sign in with `ADMIN_EMAIL` / `ADMIN_PASSWORD`.
Then go to **Settings → Users** and change the password.

The containers restart by themselves after a crash or a reboot.

## 6. Open it to the office network

From another PC, open `http://192.168.1.50:8080`. If it doesn't load, allow port 8080 through the
server's firewall:

- **Ubuntu** (only if `ufw` is enabled): `sudo ufw allow 8080/tcp`
- **Windows** (PowerShell as administrator):
  ```powershell
  New-NetFirewallRule -DisplayName "Staff Movement System" -Direction Inbound -Protocol TCP -LocalPort 8080 -Action Allow -Profile Private,Domain
  ```

Keep port 8080 closed on the router: the app is meant for the office network only.

Share the address with clerks, and give each of them their own account under **Settings → Users**.

## 7. Set up the wall TV

The TV opens a special link that shows the display board without anyone signing in. Print the link
on the server:

```sh
docker compose exec -u www-data app php artisan app:board-url
```

For one unit only, add `--unit=UGT`. The link looks like
`http://192.168.1.50:8080/board?token=3f9c…`. After it opens, the token disappears from the address
bar and the board keeps itself up to date every 15 seconds.

Ways to show it:

- **Smart TV:** open the TV's web browser and go to the link once; set it as the home page if the
  TV allows.
- **Mini PC, Chromebox or old laptop plugged into the TV** (most reliable): open the link full
  screen in Chrome. On Windows, a shortcut with this target opens it full screen at sign-in when
  placed in the Startup folder (`Win+R` → `shell:startup`):
  ```
  "C:\Program Files\Google\Chrome\Application\chrome.exe" --kiosk "http://192.168.1.50:8080/board?token=…"
  ```
  Turn off sleep and screen-off in the device's power settings.

**Treat the link like a key.** Anyone on the office network who has it can see the board (names and
whereabouts, read-only; it gives no access to the admin panel). If it leaks, change it:

1. Put a new random value in `.env.docker`, e.g. `BOARD_TOKEN=7c1e0d2b9a4f…`
   (any long random string; on Linux `openssl rand -hex 16` makes one).
2. Run `docker compose up -d`.
3. Every screen using the old link is signed out at its next refresh. Print the new link with
   `app:board-url` and open it on the TV again.

---

## Day-to-day operations

Run these in the `attendance` folder on the server.

> Always add `-u www-data` when running app commands with `docker compose exec`. Without it the
> command runs as root, and any files it writes (logs, backups) can't be written by the website.

### Updating to a new version

```sh
docker compose exec -u www-data app php artisan app:backup     # safety copy first
git pull
docker compose up -d --build
```

Database changes are applied automatically when the new version starts. Your data, settings and TV
link stay as they are.

### Backups

A backup runs every night at 01:00 and the newest 30 are kept, inside the data volume. Run one any
time:

```sh
docker compose exec -u www-data app php artisan app:backup
```

The backups live on the same machine, so **also copy them somewhere else** regularly (another PC,
a network share or a USB drive):

```sh
docker compose cp app:/var/www/html/storage/app/backups ./backups
```

This copies all backups into a `backups` folder next to the app.

### Restoring a backup

1. List the backups and pick one:
   ```sh
   docker compose exec app ls storage/app/backups
   ```
2. Stop the app and put the backup in place (replace the file name):
   ```sh
   docker compose stop
   docker compose run --rm --no-deps --entrypoint sh app -c 'cp storage/app/backups/database-2026-10-17-010000.sqlite storage/app/database.sqlite && rm -f storage/app/database.sqlite-wal storage/app/database.sqlite-shm && chown www-data:www-data storage/app/database.sqlite'
   docker compose up -d
   ```

To restore a backup you copied off the server, first put it back with
`docker compose cp ./backups/<file> app:/var/www/html/storage/app/backups/` (while the app is
running), then follow the steps above.

### Locked out of the admin account

Create a new administrator, or reset an existing user's password and make them an administrator:

```sh
docker compose exec -u www-data app php artisan app:admin someone@office.gov.my
```

It asks for the new password.

### Logs and troubleshooting

| Problem | What to do |
|---|---|
| Something went wrong and you want details | `docker compose logs --tail=100 app`; the app's own log is in the volume: `docker compose exec app ls storage/logs` |
| `app` is not **(healthy)** | `docker compose logs app` shows the start-up steps and the error |
| Port 8080 is already used on the server | In `docker-compose.yml`, change `"8080:80"` to e.g. `"8090:80"`, update `APP_URL` in `.env.docker`, then `docker compose up -d` |
| PCs can't reach the server | Check the server's IP hasn't changed, and the firewall rule in step 6 |
| The TV shows the login page | The board token changed, or the TV's browser cleared its cookies: open the link from `app:board-url` again |

### Removing everything

`docker compose down` stops the app and keeps all data. `docker compose down -v` **deletes the data
volume too**, including the database and backups. Copy the backups off first.
