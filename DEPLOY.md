SSH deploy key and manual deploy instructions
============================================

1) Create an SSH deploy key on the cPanel server

- On the cPanel server (via Terminal or SSH) run:

```bash
ssh-keygen -t ed25519 -C "deploy-key-ahhc-portal" -f ~/.ssh/ahhc_deploy_key -N ""
```

- Add the public key `~/.ssh/ahhc_deploy_key.pub` to your GitHub repository as a Deploy Key (Repository Settings → Deploy keys). Enable "Allow write access" if you prefer pushing from the server.

2) Add the private key to the server SSH agent or to `~/.ssh` and restrict permissions:

```bash
chmod 600 ~/.ssh/ahhc_deploy_key
echo "Host github.com\n  HostName github.com\n  IdentityFile ~/.ssh/ahhc_deploy_key\n  IdentitiesOnly yes" >> ~/.ssh/config
```

3) Clone the repo with SSH on the server:

```bash
cd ~/public_html
mkdir app && cd app
git clone git@github.com:championjay0007/ahhc-portal.git .
```

4) Use `scripts/deploy.sh` to update the site after each push:

```bash
cd ~/public_html/app
./scripts/deploy.sh
```

5) If you can't use SSH, use HTTPS clone and cPanel Git or manual pulls with credentials.

Security notes
- Keep `~/.ssh/ahhc_deploy_key` private and do not commit it.
- Backup your database before running migrations on production.
