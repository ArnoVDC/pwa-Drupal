let firebaseMessagingKey,
    firebaseConfig,
    firebaseMessagingToken,
    button,
    notificationsEnabled = false,
    firebaseEnabledCallback = false,
    firebaseEnabled = false;


//function requests the configuration from the Drupal backend.
function getFirebaseConfiguration() {
  let xhr = new XMLHttpRequest();
  xhr.open("GET", '/firebase-get-config', true);

  //when recieving configuration it saved the values and checks if the
  // initNotification needs to be executed.
  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
      let res = JSON.parse(xhr.response);
      firebaseConfig = res.config;
      firebaseMessagingKey = res.publicClientkey;
      if (firebaseEnabledCallback) {
        firebaseEnabledCallback = false;
        initNotification();
      }
    }
  };
  xhr.send();
}

//function checks initializes the notification object and functions that need
// to be handled.
function initNotification() {

  //check if firebase can be created
  if (!firebaseEnabled && firebaseConfig !== undefined) {
    firebase.initializeApp(firebaseConfig);
    firebaseEnabled = true;
  }
  //if there are no configurations get them and come back later.
  else if (!firebaseEnabled && firebaseConfig === undefined) {
    firebaseEnabledCallback = true;
    getFirebaseConfiguration();
    return;
  }

  messaging = firebase.messaging();

  messaging.usePublicVapidKey(firebaseMessagingKey);

  //asking permission from the user and if we have permission we send our token
  // to the backend to subscribe to notifications.
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

  //receiving a message when the window is on the foreground.
  messaging.onMessage(function (payload) {
    console.log("Message received. ", payload);
  });

  //handles when the token is refreshed so we send the new token to the backend.
  messaging.onTokenRefresh(function () {
    messaging.getToken()
        .then(function (refreshedToken) {
          firebaseMessagingToken = refreshedToken;
          sendTokenToServer(refreshedToken);
        })
        .catch(function (err) {
          notificationsEnabled = false;
          updateButtonUi();
          console.log('Unable to retrieve refreshed token ', err);
        });
  });
}

//sending the messaging token to the backend.
function sendTokenToServer(token) {
  let xhr = new XMLHttpRequest();
  xhr.open("POST", '/firebase-send-token', true);

  xhr.setRequestHeader("Content-type", "application/json");

  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
      console.info('token send to server');
    }
  };
  xhr.send('{ "token": "' + token + '" }');
}

//the cookie saves if the user already gave permission so we don't need to ask
// again.
function getCookie() {
  let enableNotificationCookie = document.cookie.replace(/(?:(?:^|.*;\s*)enableNotifications\s*\=\s*([^;]*).*$)|^.*$/, "$1");

  if (enableNotificationCookie === '') {
    document.cookie += 'enableNotifications=false;';
    enableNotificationCookie = false;
  }

  //convert string to boolean
  notificationsEnabled = enableNotificationCookie === 'true';
}

//get cookies, update button block, make sure we can receive notifications.
window.onload = function () {
  getCookie();

  if (notificationsEnabled) {
    initNotification();
  }

  button = document.getElementById('pwa_notifications');
  if (button != null) {
    updateButtonUi();
    button.addEventListener("click", buttonClick);
  }
};

//function changes the notification to enabled/disabled and saves the cookie.
function buttonClick() {
  notificationsEnabled = !notificationsEnabled;
  updateButtonUi();
  document.cookie = "enableNotifications=" + notificationsEnabled;
  if (notificationsEnabled) {
    initNotification();
  }
  else {
    messaging.deleteToken(firebaseMessagingToken);
  }
}

//changes button text.
function updateButtonUi() {
  if (button == null) {
    return;
  }
  if (notificationsEnabled) {
    button.innerText = 'Disable notifications';
  }
  else {
    button.innerText = 'Enable notifications';
  }
}
