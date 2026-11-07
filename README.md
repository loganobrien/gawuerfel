# gawuerfel
Generates a random route on Switzerland's public railway system

## Run locally for development
1. Clone repo.
2. Install frontend: `npm install`
3. Host `/api/randomTrain.php` on public webserver.
4. Change API URL in `src/store/index.js` to the one of your webserver:
```javascript
  const urls = {
    app_api: `https://gawuerfel.example.com/api`,
  }
```
5. Start frontend: `npm run dev -- --host`
6. Visit url displayed in terminal.

## Build for production
1. Run: `npm run build`
2. Upload contents of `/dist` to same public webserver.

## Dependencies
- NPM 11.14 or newer
- Webserver with PHP 8.2 or newer
- [Swiss public transport API](https://transport.opendata.ch/docs.html)