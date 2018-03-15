
var xhr = new XMLHttpRequest();
var key = '';
var firebaseEnabled = false;
var firebaseEnabledCallback = false;
xhr.open("GET", '/firebase-get-config', true);

xhr.onreadystatechange = function () {
    if (xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200) {
        var res = JSON.parse(xhr.response);
        config = res.config;
        key = res.publicClientkey;
        if(!firebaseEnabled){
            firebase.initializeApp(config);
            firebaseEnabled = true;
            if(firebaseEnabledCallback) initNotification();
        }
        
    }
};
xhr.send();

var messaging, messagingToken;

function initNotification() {

    if(!firebaseEnabled) return;
    
    messaging = '';

    messaging = firebase.messaging();

    messaging.usePublicVapidKey(key);

    messaging.requestPermission()
        .then(function () {
            updateButtonUi();
            return messaging.getToken(true)
        })
        .then(function (currentToken) {
            console.log('token: ' + currentToken + '; this was the token');
            messagingToken = currentToken;
            sendTokenToServer(currentToken);
        })
        .catch(function (err) {
            console.log('An error occurred while retrieving token. ', err);
        })
        .catch(function (err) {
            updateButtonUi();
        });

        messaging.onMessage(function (payload) {
            console.log("Message received. ", payload);
    });

    messaging.onTokenRefresh(function () {
        messaging.getToken()
            .then(function (refreshedToken) {
               messagingToken = refreshedToken;
            })
            .catch(function (err) {
                console.log('Unable to retrieve refreshed token ', err);
            });
    });

}

function sendTokenToServer(token) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", '/firebase-send-token', true);

    xhr.setRequestHeader("Content-type", "application/json");

    xhr.onreadystatechange = function () {
        if (xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200) {
            console.log('answer');
        }
    };
    xhr.send('{ "token": "' + token + '" }');
}

    var button;
    var notificationsEnabled = false;
    window.onload = function(){
        var enbaleNotificationCookie = document.cookie.replace(/(?:(?:^|.*;\s*)enableNotifications\s*\=\s*([^;]*).*$)|^.*$/, "$1");

        if(enbaleNotificationCookie == ''){
            document.cookie += 'enableNotifications=false;';
            enbaleNotificationCookie = false;
        };

        //convert string to boolean
        notificationsEnabled = enbaleNotificationCookie == 'true';

        if(notificationsEnabled){
            if(firebaseEnabled) initNotification();
            else firebaseEnabledCallback = true; 
        }
            

        button =  document.getElementById('pwa_notifications');

        updateButtonUi();
        button.addEventListener("click", buttonClick); 
};

function buttonClick(){
    notificationsEnabled = !notificationsEnabled;
    updateButtonUi();
    if(notificationsEnabled) initNotification();    
    else messaging.deleteToken(messagingToken);
    document.cookie = "enableNotifications=" + notificationsEnabled;
}


function updateButtonUi(){
    if(button == null) return;
    if(notificationsEnabled) button.innerText = 'Disable notifications';
    else button.innerText = 'Enable notifications';
}
