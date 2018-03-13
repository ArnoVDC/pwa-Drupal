  var config = {
    apiKey: "AIzaSyBt9_ILyFevekzhMyHQ9Nehh1rl8VrdsEU",
    authDomain: "bp-pwa-drupal.firebaseapp.com",
    databaseURL: "https://bp-pwa-drupal.firebaseio.com",
    projectId: "bp-pwa-drupal",
    storageBucket: "bp-pwa-drupal.appspot.com",
    messagingSenderId: "194965125810"
  };
  firebase.initializeApp(config);

  navigator.serviceWorker.register('/firebase-messaging-sw.js', {
    scope: '/'
  })
  .then((registration) => {
    console.log("sw registered")
  })
  .catch(function (err) {
    console.log("error adding sw");
  });

  const messaging = firebase.messaging();


  messaging.usePublicVapidKey("BL4qDOlR48X6HTJHjqpvERU_gVuZd1QRNfxHy4oWyzfgb17pVzpy61RUfwHpaRM6JrElIOy5ukVYndpGYW0YuIo");

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
    })

    messaging.onTokenRefresh(function() {
      messaging.getToken()
      .then(function(refreshedToken) {
        console.log('Token refreshed.');

      })
      .catch(function(err) {
        console.log('Unable to retrieve refreshed token ', err);
      });
    });


  function sendTokenToServer(token) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", '/firebase-send-token', true);

    xhr.setRequestHeader("Content-type", "application/json");


    xhr.onreadystatechange = function () {
      if (xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200) {
        console.log('answer');
      }
    }
    xhr.send('{ "token": "' + token + '" }');
  }
