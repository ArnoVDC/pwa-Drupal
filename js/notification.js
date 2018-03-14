
var xhr = new XMLHttpRequest();
xhr.open("GET", '/firebase-get-config', true);

xhr.onreadystatechange = function () {
    if (xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200) {
        var res = JSON.parse(xhr.response);
        config = res.config;
        key = res.publicClientkey;
        initFirebase(config, key);
    }
};
xhr.send();


function initFirebase(config, key) {

    firebase.initializeApp(config);
    const messaging = firebase.messaging();

    messaging.usePublicVapidKey(key);

    messaging.requestPermission()
        .then(function () {
            console.log('Notification permission granted.');
            return messaging.getToken()
        })
        .then(function (currentToken) {
            console.log('token: ' + currentToken + '; this was the token');
            sendTokenToServer(currentToken);
        })
        .catch(function (err) {
            console.log('An error occurred while retrieving token. ', err);
        })
        .catch(function (err) {
            console.log('Unable to get permission to notify.', err);
        });

    messaging.onMessage(function (payload) {
        console.log("Message received. ", payload);
    });

    messaging.onTokenRefresh(function () {
        messaging.getToken()
            .then(function (refreshedToken) {
                console.log('Token refreshed.');

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
