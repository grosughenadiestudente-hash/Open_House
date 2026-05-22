# Demo iMSTK-like (needle + soft tissue)

Questa demo è un viewer web leggero che genera una simulazione procedurale di un ago che penetra un tessuto deformabile 2D (mesh). Puoi riprodurre l'animazione nel browser e esportare i dati come `simulation.json`.

Istruzioni rapide:

- Apri [imstk_demo/index.html](imstk_demo/index.html) in un browser moderno (Chrome, Edge, Firefox).
- Usa `Play` / `Pause` per controllare l'animazione.
- Imposta il numero di `Frames` e premi `Esporta JSON` per salvare i dati della simulazione.

Hosting e distribuzione

- Su XAMPP (locale): copia la cartella `imstk_demo` dentro la root di Apache (es. `C:\xampp\htdocs\Open_House\imstk_demo` già presente). Apri il browser a:

```text
http://localhost/Open_House/imstk_demo/index.html
```

- Per rendere la demo pubblica (static site): puoi usare GitHub Pages o Netlify.
	- GitHub Pages: crea un repository, push della cartella `imstk_demo` nella branch `gh-pages` o nelle impostazioni Pages scegli `main`/`docs` e posiziona i file nella cartella `docs`.
	- Netlify: trascina la cartella `imstk_demo` nella dashboard (drag & drop) o collega il repo; Netlify serve automaticamente il sito.

Caricare dati precomputati

- Se vuoi che tutti gli utenti vedano la stessa simulazione senza doverla calcolare lato client, inserisci un file `data/simulation.json` nella cartella `imstk_demo/data/`.
- L'interfaccia prova automaticamente a caricare `data/simulation.json` e permette anche di caricare un file JSON manualmente tramite il pulsante `Carica file`.

Integrazione nella tua applicazione

- Puoi includere `imstk_demo/index.html` in un iframe nella tua pagina PHP o aggiungere un link nel `navbar.php` per aprire la demo.
- Se preferisci, posso generare un loader C++/C# che legge il JSON e ricostruisce la mesh per una versione desktop nativa.

Formato esportato (JSON):

- `meta`: proprietà `nx`, `ny`, `size`, `frames`.
- `frames`: array di oggetti con `needle` (x,y,z) e `zs` (array di z per ogni vertice, ordine row-major).

Note e prossimi passi consigliati:

- Posso modificare il generatore per adattare la risoluzione, la scala o il modello di deformazione.
- Se preferisci file JSON pronti, posso generare e inserire `imstk_demo/data/simulation.json` con configurazione scelta.
- Se vuoi integrare dati in un'applicazione desktop nativa, posso convertire il JSON in CSV o binario e preparare un piccolo loader C++/C#.
