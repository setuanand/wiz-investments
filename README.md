# Wiz Investments Website

A local WordPress website prototype for Wiz Investments, built with a custom WordPress theme and Docker development environment.

## Features

- Custom `Wiz Theme` in `wp-content/themes/wiz-theme`
- Responsive homepage with service cards and a simulation analytics preview
- Interactive analytics dashboard with scenarios and Chart.js visualization
- Secure contact form with admin submission handling
- Docker Compose environment for local development
- GitHub-ready repository structure and CI workflow

## Local Setup

```bash
cd /home/seanand/projects/wiz-investments
docker compose up -d
```

Then open:

```text
http://localhost:8000
```

Complete the WordPress installer and log in to WP Admin.

## Theme Activation

1. Open **Appearance > Themes**.
2. Activate the `Wiz Theme`.
3. Create the following pages under **Pages > Add New** if not already present:
   - About
   - Services
   - Contact
   - Analytics Dashboard

The theme auto-creates missing pages for admins, but you can also manually add them and select the correct page template.

## GitHub Setup

Initialize the repository locally:

```bash
git init
git add .
git commit -m "Initial Wiz Investments website scaffold"
```

Add your GitHub remote and push:

```bash
git remote add origin git@github.com:YOUR_USERNAME/wiz-investments.git
git branch -M main
git push -u origin main
```

## Development Notes

- Theme source: `wp-content/themes/wiz-theme`
- Docker Compose config: `docker-compose.yml`
- Ignore WordPress core and uploads via `.gitignore`

## Next Improvements

- Add user membership and paid access gating for analytics
- Build account pages and saved simulation history
- Deploy to a managed WordPress host or connect the theme to GitHub for version control
