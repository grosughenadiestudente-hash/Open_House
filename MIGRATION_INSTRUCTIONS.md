# 📚 Guida di Migrazione: istituti → istituti_e_partner

## 🎯 Obiettivo
Aggiornare la struttura del database rinominando la tabella `istituti` in `istituti_e_partner` e rinominando i campi secondo la nuova nomenclatura, oltre ad aggiungere nuovi campi per il supporto dei partner.

## 📋 Riepilogo Modifiche

### Rinominazione Tabella
- **Vecchio nome**: `istituti`
- **Nuovo nome**: `istituti_e_partner`

### Rinominazione Colonne

| Vecchio Nome | Nuovo Nome | Note |
|---|---|---|
| `id` | `ID_Ente` | Primary Key, AUTO_INCREMENT |
| `codice_istituto` | `Cod_Mecc` | Codice Meccanografico (opzionale per aziende) |
| `nome` | `Ragione_Sociale` | Nome legale dell'ente |
| `tipo_scuola` | `Tipologia` | Tipo di ente (SCUOLA, AZIENDA, etc.) |
| `email` | `Email` | Email di contatto |
| `indirizzo` | `Indirizzo` | Indirizzo fisico |
| `comune` | `Comune` | Comune |
| `provincia` | `Provincia` | Provincia |
| `regione` | `Regione` | Regione |

### Nuove Colonne Aggiunte

| Colonna | Tipo | Descrizione |
|---|---|---|
| `CF_PIVA` | varchar(20) | Codice Fiscale o Partita IVA |
| `Cod_REA` | varchar(20) | Codice REA/SDI (opzionale, solo per aziende) |
| `Coordinate_GPS` | varchar(100) | Coordinate geografiche (lat,lon) |
| `Stato_Validazione` | tinyint(1) | 0=In attesa, 1=Approvato, 2=Bloccato |

## 🚀 Come Eseguire la Migrazione

### Opzione 1: Script Web (Consigliato per principianti)

1. Accedi al browser e vai a: `http://localhost/Open_House/migrate_istituti.php`
2. Leggi attentamente le modifiche che verranno applicate
3. Spunta la checkbox di conferma
4. Clicca sul pulsante "Esegui Migrazione"
5. Attendi il completamento e verifica il risultato
6. **Elimina il file `migrate_istituti.php`** dopo il completamento

### Opzione 2: MySQL/phpmyadmin

1. Apri phpMyAdmin
2. Seleziona il database `open_house` (o il tuo database)
3. Vai nella sezione "SQL"
4. Copia e incolla il contenuto da `database/migration_istituti_to_istituti_e_partner.sql`
5. Clicca "Esegui"

### Opzione 3: Riga di Comando

```bash
# Dalla cartella del progetto
mysql -u utente -p database_name < database/migration_istituti_to_istituti_e_partner.sql

# Oppure
mysql -h localhost -u root -p open_house < database/migration_istituti_to_istituti_e_partner.sql
```

## ✅ Verifiche Post-Migrazione

### 1. Verifica la struttura della tabella
```sql
DESCRIBE istituti_e_partner;
-- Oppure in phpMyAdmin: Vai al tab "Struttura"
```

### 2. Verifica il conteggio dei dati
```sql
SELECT COUNT(*) FROM istituti_e_partner;
-- Deve corrispondere al numero di righe della vecchia tabella
```

### 3. Verifica i dati di esempio
```sql
SELECT ID_Ente, Ragione_Sociale, Tipologia, Comune, Provincia FROM istituti_e_partner LIMIT 5;
```

### 4. Test dell'applicazione
- Accedi al sito: `http://localhost/Open_House/`
- Verifica che le liste istituti si carichino correttamente
- Verifica che i dettagli istituto siano visibili
- Verifica che le attività siano associate correttamente agli istituti

## 🔄 Compatibilità Codice PHP

✅ **Tutti i file PHP sono già compatibili con la nuova struttura!**

Il codice PHP utilizza alias SQL per mantenere la compatibilità:
```php
SELECT i.ID_Ente as id, 
       i.Ragione_Sociale as nome, 
       i.Tipologia as tipo_scuola,
       i.Cod_Mecc as codice_istituto
FROM istituti_e_partner i
```

Non è necessario modificare alcun file PHP!

## 💾 Backup

Dopo l'esecuzione della migrazione:
- ✓ Una copia della vecchia tabella sarà salvata come `istituti_backup_legacy`
- ✓ La vecchia tabella `istituti` rimane nel database ma non più utilizzata
- ✓ Puoi eliminare la vecchia tabella dopo aver verificato tutto

## 🆘 Rollback (In caso di problemi)

Se qualcosa va storto:

```sql
-- Ripristinare da backup
DROP TABLE IF EXISTS istituti_e_partner;
RENAME TABLE istituti_backup_legacy TO istituti_e_partner;
```

Oppure:
```sql
-- Ripristinare il database dal backup precedente
-- (se hai un backup dei file del database)
```

## 📞 Contatti / Supporto

In caso di problemi durante la migrazione:
1. Controlla i log di errore in `MIGRATION_LOG.md`
2. Verifica la connessione al database
3. Assicurati di avere permessi sufficienti per modificare il database
4. Consulta la documentazione di migrazione nella radice del progetto

## 🎉 Completamento

Una volta completata la migrazione:
1. ✅ Elimina il file `migrate_istituti.php`
2. ✅ Elimina il file di backup `istituti_backup_legacy` se non più necessario
3. ✅ La tabella `istituti` può essere deletata se confermato che tutto funziona
4. ✅ Aggiorna la documentazione del progetto

---

**Data migrazione**: 3 Maggio 2026  
**Sistema**: Open House VR  
**Status**: Pronto per la migrazione
