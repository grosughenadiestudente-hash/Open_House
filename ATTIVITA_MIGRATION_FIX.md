# 🔧 Correzione Tabella "attivita" → "attivita_eventi"

**Data**: 2026-05-03  
**Errore Originale**: `SQLSTATE[42S02]: Base table or view not found: 1146 Table 'open_house.attivita' doesn't exist`  
**Status**: ✅ CORRETTO - 12 file aggiornati

---

## 🔍 Problema Diagnosticato

La tabella `attivita` è stata rinominata a `attivita_eventi` durante la migrazione del database, ma il codice PHP continuava a fare riferimento alla vecchia tabella.

**Errore**: 
```
Fatal error: Uncaught PDOException: SQLSTATE[42S02]: Base table or view not found: 1146 Table 'open_house.attivita' doesn't exist 
in C:\xampp\htdocs\Open_House\istituti_elenco.php:70
```

---

## ✅ Soluzioni Implementate

### Cambio della Tabella

| Aspetto | Prima | Dopo |
|--------|-------|------|
| **Tabella** | `attivita` | `attivita_eventi` |
| **ID Attività** | `a.id` | `a.ID_Attivita` |
| **FK Ente** | `a.istituto_id` | `a.FK_Ente_Organizzatore` |
| **Titolo** | `a.titolo` | `a.Titolo` |
| **Descrizione** | `a.descrizione` | `a.Descrizione` |
| **Data/Ora** | `a.data_ora` | `a.Data_Ora` |
| **Max Posti** | `a.max_partecipanti` | `a.Max_Posti` |
| **Supporta VR** | `a.supporta_vr` | `a.Supporta_VR` |
| **Tipo Attività** | `a.tipo_attivita` | `a.Tipo_Attivita` |
| **Link WebXR** | `a.link_webxr` | `a.Link_WebXR` |
| **Materiali** | `a.materiali` | `a.Materiali_URL` |
| **Stato** | `a.stato` | `a.Stato` |

---

## 📝 File Corretto

### **1. index.php** - ✅ CORRETTO
- **Riga 13-28**: SELECT per attività featured
- **Riga 30**: COUNT attività pubblicate
- **Cambios**: FROM attivita → FROM attivita_eventi, alias aggiunti per tutte le colonne

### **2. istituti_elenco.php** - ✅ CORRETTO
- **Riga 38-40**: LEFT JOIN attivita → LEFT JOIN attivita_eventi
- **Cambios**: istituto_id → FK_Ente_Organizzatore, a.id → a.ID_Attivita, stato → Stato

### **3. attivita_elenco.php** - ✅ CORRETTO
- **Riga 8**: SELECT query principale
- **Riga 24**: AND a.tipo_attivita → AND a.Tipo_Attivita
- **Riga 28**: GROUP BY a.id → GROUP BY a.ID_Attivita

### **4. attivita_dettaglio.php** - ✅ CORRETTO
- **Riga 9-16**: SELECT con JOIN completamente riscritto con alias

### **5. attivita_modifica.php** - ✅ CORRETTO
- **Riga 10-11**: WHERE ID_Attivita = ? AND FK_Ente_Organizzatore = ?

### **6. attivita_gestione.php** - ✅ CORRETTO
- **Riga 9-16**: SELECT da attivita_eventi con alias

### **7. attivita_partecipa.php** - ✅ CORRETTO
- **Riga 18-23**: SELECT con JOIN aggiunti alias

### **8. api_istituti.php** - ✅ CORRETTO
- **Riga 16-19**: LEFT JOIN attivita → LEFT JOIN attivita_eventi

### **9. dashboard_istituto.php** - ✅ CORRETTO
- **Riga 9**: COUNT FROM attivita_eventi
- **Riga 13-15**: JOIN attivita_eventi
- **Riga 21-28**: SELECT attività recenti

### **10. dashboard_utente.php** - ✅ CORRETTO
- **Riga 9-16**: Prenotazioni dell'utente
- **Riga 18-29**: Attività disponibili

### **11. istituto_dettaglio.php** - ✅ CORRETTO
- **Riga 17-24**: Attività dell'istituto

### **12. prenota.php** - ✅ CORRETTO
- **Riga 20-21**: SELECT Max_Posti as max_partecipanti, Stato as stato

---

## 🗂️ Schema di Mappatura Alias

Per mantenere la compatibilità con il codice HTML, tutte le query usano alias per le colonne:

```php
// Esempio di SELECT con alias
SELECT 
  a.ID_Attivita as id,
  a.Titolo as titolo,
  a.Descrizione as descrizione,
  a.Data_Ora as data_ora,
  a.Supporta_VR as supporta_vr,
  a.Max_Posti as max_partecipanti,
  a.Tipo_Attivita as tipo_attivita,
  a.Link_WebXR as link_webxr,
  a.Materiali_URL as materiali,
  a.Stato as stato,
  a.created_at
FROM attivita_eventi a
```

---

## ✨ Verifica Completate

- ✅ Tutti i 12 file aggiornati
- ✅ Nessun errore di sintassi PHP (verificato con `php -l`)
- ✅ Tabella `attivita_eventi` esiste nel database
- ✅ Alias corrispondono ai nomi usati nei template HTML
- ✅ Join corretti con `istituti_e_partner` su `FK_Ente_Organizzatore`
- ✅ Join corretti con `prenotazioni` su `ID_Attivita`

---

## 🎯 Risultato Finale

Dopo queste correzioni:
- ✅ La pagina `istituti_elenco.php` non darà più l'errore "tabella non trovata"
- ✅ Tutte le query useranno la tabella corretta `attivita_eventi`
- ✅ I dati delle attività (quando inseriti) verranno recuperati correttamente
- ✅ I template HTML funzioneranno senza modifiche aggiuntive grazie agli alias

---

## 📊 Stato Database

- **Tabella `attivita_eventi`**: ✅ Esiste, 0 record (vuota - nessun dato inserito ancora)
- **Tabella `istituti_e_partner`**: ✅ Esiste, 7.861 record
- **Tabella `prenotazioni`**: ✅ Esiste, 0 record
- **Tabella `utenti_finali`**: ✅ Esiste, 1 record

---

## 🚀 Prossimi Passi

1. **Test pagina**: Visita [istituti_elenco.php](http://localhost/Open_House/istituti_elenco.php)
2. **Verifica**: La pagina dovrebbe caricarsi senza errori
3. **Inserimento dati**: Aggiungi attività tramite dashboard istituto
4. **Verifica visualizzazione**: Controlla che le attività compaiano nelle liste

---

## 📝 Note di Implementazione

- Tutti i nomi di colonna usano la corretta capitalizzazione (es. `Stato` non `stato`)
- Gli alias mantengono il naming convention originale lowercase per compatibilità con il codice HTML
- Le query JOIN usano i nomi di colonna esatti: `FK_Ente_Organizzatore`, `ID_Attivita`
- Verificate che le colonne usate nei GROUP BY siano coerenti con le colonne SELECT

---

**Status**: 🟢 COMPLETATO  
**File Modificati**: 12  
**Errori Risolti**: 1 (tabella non trovata)  
**Test**: ✅ Sintassi verificata

