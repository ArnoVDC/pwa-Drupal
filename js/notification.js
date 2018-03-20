//string
var firebaseMessagingKey,
  firebaseConfig,
  firebaseMessaging,
  firebaseMessagingToken;

var button;
var notificationsEnabled = false;
var firebaseEnabledCallback = false;
var firebaseEnabled = false;

getFirebaseConfiguration();


function getFirebaseConfiguration() {
  var xhr = new XMLHttpRequest();
  //first get the firebase config from drupal
  xhr.open("GET", '/firebase-get-config', true);

  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
      var res = JSON.parse(xhr.response);
      firebaseConfig = res.config;
      firebaseMessagingKey = res.publicClientkey;
      if(firebaseEnabledCallback) {
        firebaseEnabledCallback = false;
        initNotification();
      }
    }
  };
  xhr.send();
}



function initNotification() {

  //check if firebase can be created else return
  if (!firebaseEnabled && firebaseConfig !== undefined) {
    firebase.initializeApp(firebaseConfig);
    firebaseEnabled = true;
  }
  else if (!firebaseEnabled && firebaseConfig === undefined) {
    firebaseEnabledCallback = true;
    getFirebaseConfiguration();
    return;
  } 
  
  messaging = firebase.messaging();

  messaging.usePublicVapidKey(firebaseMessagingKey);

  messaging.requestPermission()
    .then(function () {
      updateButtonUi();
      return messaging.getToken(true)
    })
    .then(function (currentToken) {
      firebaseMessagingToken = currentToken;
      sendTokenToServer(currentToken);
    })
    .catch(function (err) {
      console.log('An error occurred while retrieving token. ', err);
      notificationsEnabled = false;
      updateButtonUi();
    });

  messaging.onMessage(function (payload) {
    console.log("Message received. ", payload);
  });

  messaging.onTokenRefresh(function () {
    messaging.getToken()
      .then(function (refreshedToken) {
        firebaseMessagingToken = refreshedToken;
      })
      .catch(function (err) {
        notificationsEnabled = false;
        updateButtonUi();
        console.log('Unable to retrieve refreshed token ', err);
      });
  });
}

function sendTokenToServer(token) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", '/firebase-send-token', true);

  xhr.setRequestHeader("Content-type", "application/json");

  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200)
      console.info('token send to server');
  };
  xhr.send('{ "token": "' + token + '" }');
}

function getCookie(){
  var enbaleNotificationCookie = document.cookie.replace(/(?:(?:^|.*;\s*)enableNotifications\s*\=\s*([^;]*).*$)|^.*$/, "$1");

  if (enbaleNotificationCookie === '') {
    document.cookie += 'enableNotifications=false;';
    enbaleNotificationCookie = false;
  }

   //convert string to boolean
   notificationsEnabled = enbaleNotificationCookie === 'true';

}


window.onload = function () {
  getCookie();
  
  if (notificationsEnabled) {
   initNotification();
  }

  button = document.getElementById('pwa_notifications');
  if(button != null){
    updateButtonUi();
    button.addEventListener("click", buttonClick);
  }  
};

function buttonClick() {
  notificationsEnabled = !notificationsEnabled;
  updateButtonUi();
  document.cookie = "enableNotifications=" + notificationsEnabled;
  if (notificationsEnabled) initNotification();
  else messaging.deleteToken(firebaseMessagingToken);
}


function updateButtonUi() {
  if (button == null) return;
  if (notificationsEnabled) button.innerText = 'Disable notifications';
  else button.innerText = 'Enable notifications';
}
