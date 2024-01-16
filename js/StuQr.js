


// big async function allowing language loading
async function fetchLanguageData() {

    // all data about language
    let lang_data;

    // language (fr / en etc.)
    let lang = "";
    
    // get lang
    const urlSearchParams = new URLSearchParams(window.location.search);
    lang = urlSearchParams.get(lang);
    
    // if not lang
    if (lang == null){

        // use navigator lang
        lang = navigator.language || navigator.userLanguage;
        lang = lang.substring(0, 2);

    }

    // try to get and load language json file
    const response = await fetch(`/lang/${lang}.json`);
    if (response.ok) {
        lang_data = await response.json();
    } else {
        // else use default language
        const defaultResponse = await fetch('/lang/en.json');
        lang_data = await defaultResponse.json();
    }


    // for history, check if old qr code exist on server (30 days history max)
    function checkQRCodeExists(qrCodeName) {
        
        const url = `/qrgen/${qrCodeName}.png`;
        return fetch(url)
            .then(response => response.ok)
            .catch(error => {
                return false;
            });
    }

    // function that load more history by 4 steps of history
    function loadMoreQRHistory(existingQRCodeNames) {

        const historyRow = document.getElementById('historow');
        
        //remove the more history button
        const voirPlusButtonOld = document.getElementById('voirPlusButton');
        if (voirPlusButtonOld) {
            voirPlusButtonOld.remove();
        }

        // number of element about already displayed history element
        const displayedElements = historyRow.getElementsByClassName('histo-element').length;

        // if not all history
        if (displayedElements < existingQRCodeNames.length) {

            // define remaining element to display
            const remainingElements = existingQRCodeNames.length - displayedElements;

            // define a step of 6 elements
            const elementsToLoad = Math.min(remainingElements, 6); 

            // loop that display more
            for (let i = displayedElements; i < displayedElements + elementsToLoad; i++) {

                // if not over limit
                if (i >= existingQRCodeNames.length) {
                    break; 
                }

                // get name of actual qrname
                const qrCodeName = existingQRCodeNames[i];

                // add the history div of the current historic qr
                const historyElement = document.createElement('div');
                historyElement.classList.add('col-12', 'col-sm-6', 'col-md-4', 'col-lg-3', 'col-xl-2', 'histo-element', 'mb-5');


                const qrImageDiv = document.createElement('div');
                qrImageDiv.classList.add('qrgen2');

                const qrImage = document.createElement('img');
                qrImage.classList.add('qrgen2');
                qrImage.src = `./qrgen/${qrCodeName}.png`;
                qrImage.alt = 'QR Code';

                qrImageDiv.appendChild(qrImage);

                const downloadDiv = document.createElement('div');
                downloadDiv.classList.add('mt-5');

                const downloadLink = document.createElement('a');
                downloadLink.href = `./qrgen/${qrCodeName}.png`;
                downloadLink.setAttribute('download', `${qrCodeName}.png`);

                const downloadButton = document.createElement('p');
                downloadButton.classList.add('qrbutton2');

                downloadButton.textContent = lang_data.download;

                downloadLink.appendChild(downloadButton);
                downloadDiv.appendChild(downloadLink);

                historyElement.appendChild(qrImageDiv);
                historyElement.appendChild(downloadDiv);

                historyRow.appendChild(historyElement);
            }
        }

        // recalculate now where we are
        const displayedElements2 = historyRow.getElementsByClassName('histo-element').length;

        // if i'm not at the end of history
        if (displayedElements2 <  existingQRCodeNames.length) {

            // display more history button
            const divVoirPlus = document.createElement('div');
            divVoirPlus.classList.add('col-12', 'd-flex', 'flex-column',  'align-items-center');    

            const voirPlusButton = document.createElement('p');

            voirPlusButton.textContent = lang_data.seeMore;

            voirPlusButton.id = 'voirPlusButton';
            voirPlusButton.classList.add('qrbutton2', 'mb-5');

            voirPlusButton.addEventListener('click', () => {

                loadMoreQRHistory(existingQRCodeNames);

            });

            divVoirPlus.appendChild(voirPlusButton);
            historyRow.appendChild(divVoirPlus);
        }

    }

    // build history if user give autorisation
    function buildQRCodeHistory() {

        if (localStorage.getItem("consentGiven") === 'true'){

            // get and parse qrCodeHistory  from local storage
            const qrCodeHistory = JSON.parse(localStorage.getItem('qrCodeHistory')) || [];

            const existingQRCodeNames = [];

            // promises that find existing qr code from server and delete from localstorage qr code that are older than 30 days
            const fetchPromises = qrCodeHistory.map(qrCodeData => {
                const qrCodeName = JSON.parse(qrCodeData).qrCodeName;

                return checkQRCodeExists(qrCodeName).then(exists => {

                    // the qr exist
                    if (exists) {
                        // push on temp list the qr
                        existingQRCodeNames.push(qrCodeName);
                    } else {

                        // find qr in index
                        const indexToRemove = qrCodeHistory.findIndex(data => {
                            return JSON.parse(data).qrCodeName === qrCodeName;
                        });

                        // if the qr is find in history, remove it
                        if (indexToRemove !== -1) {
                            qrCodeHistory.splice(indexToRemove, 1);

                            localStorage.setItem('qrCodeHistory', JSON.stringify(qrCodeHistory));
                        }
                    }
                });
            });

            // for all fetch promises from checkQRCodeExists
            Promise.all(fetchPromises)
                .then(() => {

                    // if there are more than 0 codes in the history that still exist
                    if (existingQRCodeNames.length > 0) {

                        // get historow and load first step of history
                        const historyRow = document.getElementById('historow');
                        loadMoreQRHistory(existingQRCodeNames);
                        

                    }else{

                        // display none for history title
                        var htitleElement = document.getElementById('htitle');
                        htitleElement.style.display = 'none';

                    }


                })
                .catch(error => {
                    // show error
                    if (navigator.language.startsWith('fr')) {
                        console.error("Impossible d'ajouter l'historique", error);
                    }else{
                        console.error('Unable to add your history', error);
                    }
                });
        
        }
    }

    buildQRCodeHistory();

}

// Function that add new generated qr code to localStorage for history
function addToQRCodeHistory(newQRCodeName) {
    let qrCodeHistory = JSON.parse(localStorage.getItem('qrCodeHistory')) || [];
    qrCodeHistory.push(newQRCodeName);
    localStorage.setItem('qrCodeHistory', JSON.stringify(qrCodeHistory));
}

// Get History parsed from json
function getQRCodeHistory() {
    return JSON.parse(localStorage.getItem('qrCodeHistory')) || [];
}        


// showing Cosent Modal for history if user changes his mind
function showConsentModal() {
    
    const modal = document.getElementById('consentModal');
    modal.style.display = 'flex';

}

// Do history with consent or not
function handleConsent(consentGiven) {
    const historow = document.getElementById('historow');
    const modal = document.getElementById('consentModal');

    // if consent
    if (consentGiven) {

        localStorage.setItem('consentGiven', 'true');

        // Show user history with consent
        modal.style.display = 'none';
        historow.style.display = 'block';
    } else {

        // user dont consent
        localStorage.setItem('consentGiven', 'false');
        modal.style.display = 'none';

    }
}

// show text in browser language
fetchLanguageData();