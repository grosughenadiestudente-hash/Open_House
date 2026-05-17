function validateAndSubmit(){

//****M: selezione dei dati del form
    const fname = document.getElementById('fname').value;
    const lname = document.getElementById('lname').value;
    const sex = checkRadio(document.getElementsByName('sex')); // selezione del sesso
    const bloodType = document.getElementById("bloodType").value;
    const birth = document.getElementById('birth').value;
    const day = document.getElementById("day").value;
    const location = document.getElementById("location").value;
    var cc = 150;
    var age = calcAge(birth);

    //****M: controllo compilazione campi (usando un array)
    if ( catchErrors( [fname, lname, sex, bloodType, birth, day, location] ) ){
        alert("ATTENZIONE! Ci sono dei campi non compilati correttamente")
        return false;
    }

    // ****M verifica età consentita per effettuare donazioni/prelievi di sangue
    if(age < 18 || age > 110){
        alert("ATTENZIONE! Non si ha un età consona per effettuare donazioni/prelievi, solo analisi del sangue");
        return false;
    }

    // ****M: calcolo orario prelievo
    var time = "8.30";
    if(day != 7){
        if((day % 2) == 1){
            time = "10.30";
        }
    }else{
        time = "11.30";
    }

    // ****M: calcolo dei cc di sangue
    if(age < 25){
        cc = 150;
    }else if(age < 35){
        cc = 250;
    }else{
        cc = 200;
    }

    if(sex == "female"){
        cc /= 2;
    }

    // Visualizza i dati in console
    console.log("Nome: " + fname);
    console.log("cognome: " + lname);
    console.log("Birthday: " + birth);
    console.log("sex: " + sex);
    console.log("blood type: " + bloodType);
    console.log("day: " + day);
    console.log("location: " + location);
    console.log("time: " + time);
    console.log("age: " + age);

    return true;
}

// ****M: calcolo età
function calcAge (birthday) {
    birthday = new Date(birthday);
    today = new Date();
 
    var years = (today.getFullYear() - birthday.getFullYear());
 
    if (today.getMonth() < birthday.getMonth() || 
        today.getMonth() == birthday.getMonth() && today.getDate() < birthday.getDate()) {
        years--;
    }
 
    return years;
}

//****M: Ritorno alla pagina iniziale (ristampa dei form e rimozione del messaggio dei risultati) */
function reset(){
    document.getElementById("results").innerHTML = "";
}

// ****M: verifica sesso
function checkRadio(radios) {
    // Seleziona tutte le opzioni radio con lo stesso 'name'
    // const radios = document.getElementsByName('sex');
    let selectedValue = '';
  
    // Scorre le opzioni per vedere quale è selezionata
    for (let i = 0; i < radios.length; i++) {
      if (radios[i].checked) {
        selectedValue = radios[i].value;
        break; // Esce dal ciclo una volta trovata l'opzione selezionata
      }
    }
  
    // Mostra il risultato
    return selectedValue;
}

function calcColoredOs(cc, bloodType){ //****M: calcolo del numero e colore delle "o"
    let seriesOfOs = "";
    let totOs = cc/10;

    /* Colori gruppo sanguigno
    red = 0
    blue = A
    green = B
    yellow = AB
    */

    let color = "red";

    for(let i = 0; i < totOs; i++){
        seriesOfOs += "o";
    }

    if(bloodType == "a"){
        color = "blue";
    }else if(bloodType == "b"){
        color = "green";
    }else if(bloodType == "ab"){
        color = "yellow";
    }else{}

    return "<p id=\"coloredOsCc\" style=\"color:"+color+";\">" + seriesOfOs + "</p>"; //paragrafo html con le "o" colorate
}

function catchErrors(data){ // let data = [fname, lname, sex, bloodType, birth, day, location]
    let myErrors = false;

    //****M: dreazione di un array dataNames per agevolare l'invio dei nomi dei vari errori
    let dataNames = ["sex", "bloodType", "birth", "day", "location"]; //****M: Omesse i nomi "fname" e "lname" perchè non necessarie

    for(let i = 1; i < data.length; i++){

        if(i == 1){ //****M: controllo nomi (fname e lname)
            if(data[0].length < 2 || data[i].length < 2){
                document.getElementById("nameError").innerHTML = "*Nome o cognome non compilati/troppo corti*";
                myErrors = true;
            }else{
                //****M: ripristinamento semplice del campo errore (sostituzione con una stringa vuota)
                document.getElementById("nameError").innerHTML = "";
            }

        }else if(i == 5){ //****M: controllo giorno (day) di tipo int
            if(data[i] == 0){
                document.getElementById("dayError").innerHTML = "*campo non compilato*";
                myErrors = true;
            }else{
                //****M: ripristinamento semplice del campo errore (sostituzione con una stringa vuota)
                document.getElementById("dayError").innerHTML = "";
            }

        }else{ //****A: controllo di tutti dati restanti (sex, bloodType, birth, day, location)
            if(data[i] == ""){
                document.getElementById(dataNames[i-2]+"Error").innerHTML = "*campo non compilato*";
                myErrors = true;
            }else{
                document.getElementById(dataNames[i-2]+"Error").innerHTML = "";
            }
        }
    }

    return myErrors;
}