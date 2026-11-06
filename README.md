# gawuerfel
Generates a random route on Switzerland's public railway system

## Run locally for development
1. Clone repo.
2. Install frontend: `npm install`
3. Host `/api/randomTrain.php` on public webserver.
4. Change URL in `src/store/index.js`
```javascript
let app_api_url = `${protocol}//gawuerfel.example.com/api`;
```
5. Start frontend: `npm run dev -- --host`
6. Visit url displayed in terminal.

## Build for production
1. Run: `npm run build`
2. Upload contents of `/dist` to same public webserver.

## Dependencies
[Swiss public transport API](https://transport.opendata.ch/docs.html)