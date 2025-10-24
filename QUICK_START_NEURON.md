# ⚡ Quick Start - Neuron Website Agent

## 1️⃣ Verificare che Neuron sia Installato

```bash
php artisan -V
composer show neuron-core/neuron-ai
```

Deve dare: `neuron-core/neuron-ai 2.6+`

## 2️⃣ Verificare il File .env

Assicurati di avere:
```env
OPENAI_API_KEY=sk-...
OPENAPI_KEY=sk-...
```

## 3️⃣ Testare con CURL

```bash
curl -N "http://localhost:8000/api/chatbot/neuron-website-stream?message=Ciao&team=cavallini&locale=it"
```

Deve ricevere: evento SSE con streaming di token

## 4️⃣ Test JavaScript (Browser Console)

```javascript
// Copia/Incolla in console del browser
const es = new EventSource('/api/chatbot/neuron-website-stream?message=Quelli+servizi+offrite&team=cavallini&locale=it');
es.addEventListener('message', e => console.log(JSON.parse(e.data)));
es.addEventListener('done', () => { console.log('✅ Done'); es.close(); });
es.addEventListener('error', e => { console.error('❌ Error', e); es.close(); });
```

## 5️⃣ Check Logs

```bash
tail -f storage/logs/laravel.log | grep "NeuronWebsiteStreamController"
```

Deve mostrare: thread_id, team_slug, status

## 6️⃣ Disattivare il Vecchio Endpoint (Opzionale)

In `routes/api.php`:
```php
// Commenta la vecchia rotta
// Route::get('/chatbot/website-stream', [RealtimeChatWebsiteController::class, 'websiteStream']);
```

## 7️⃣ Verificare Database

Check che il team esista:
```bash
php artisan tinker
Team::where('slug', 'cavallini')->first();
```

Deve ritornare il team object

## 8️⃣ Trigger Manuale di Tool

Scrivi un messaggio che triggeri un tool:

- **"Quali servizi offrite?"** → `getProductInfo`
- **"Dov'è la vostra azienda?"** → `getAddressInfo`
- **"Orari disponibili?"** → `getAvailableTimes`
- **"Voglio prenotare"** → `createOrder` + `getAvailableTimes`
- **"Di quale domanda frequente?"** → `getFAQs`

## 9️⃣ Troubleshooting

### 🔴 "No streaming"
- Controlla CORS headers
- Verifica `output_buffering = Off` in php.ini
- Check logs per errori

### 🔴 "Team non trovato"
- Verifica lo slug sia corretto
- Controlla database con tinker
- Check query parameter 'team'

### 🔴 "Tools non eseguiti"
- Verifica che il tool name corrisponda nel prompt
- Check che il provider supporti tools
- Leggi i logs per dettagli

### 🔴 "Timeout"
- Aumenta max_execution_time
- Controlla la velocità di OpenAI API
- Verifica che il website content non sia troppo grande

## 🔟 Testare i Tools Singolarmente

### Test Tool: getProductInfo
```bash
curl "http://localhost:8000/api/chatbot/neuron-website-stream?message=Mostrami+i+tuoi+prodotti&team=cavallini"
```

### Test Tool: getAddressInfo
```bash
curl "http://localhost:8000/api/chatbot/neuron-website-stream?message=Qual+è+il+vostro+indirizzo&team=cavallini"
```

### Test Tool: createOrder
```bash
curl "http://localhost:8000/api/chatbot/neuron-website-stream?message=Voglio+ordinare+qualcosa&team=cavallini&uuid=test-uuid-123"
```

## 📊 Status Check

```php
// Tinker
DB::table('quoters')->where('thread_id', 'your-thread-id')->get();
// Deve mostrare la conversazione salvata
```

---

**✅ Se tutto funziona:** Congratulazioni! Il nuovo Neuron Agent è operativo!

**❌ Se hai problemi:** 
1. Leggi NEURON_WEBSITEAGENT_SETUP.md
2. Controlla i logs
3. Verifica .env e database
4. Test con curl prima di JavaScript

---

**Prossimi Step:**
- Personalizza il system prompt in `resources/lang/it/enjoywork3d_prompts.php`
- Integra nel tuo frontend
- Monitor performance con Inspector (optional)
- Disattiva il vecchio endpoint quando stabile
