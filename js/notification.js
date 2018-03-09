(function () {
  var config = {
    apiKey: "AIzaSyBt9_ILyFevekzhMyHQ9Nehh1rl8VrdsEU",
    authDomain: "bp-pwa-drupal.firebaseapp.com",
    databaseURL: "https://bp-pwa-drupal.firebaseio.com",
    projectId: "bp-pwa-drupal",
    storageBucket: "bp-pwa-drupal.appspot.com",
    messagingSenderId: "194965125810"
  };
  firebase.initializeApp(config);

  const messaging = firebase.messaging();
  messaging.usePublicVapidKey("BL4qDOlR48X6HTJHjqpvERU_gVuZd1QRNfxHy4oWyzfgb17pVzpy61RUfwHpaRM6JrElIOy5ukVYndpGYW0YuIo");

  messaging.requestPermission()
    .then(function () {
      console.log('Notification permission granted.');
      return messaging.getToken()
    })
    .then(function (currentToken) {
      if (currentToken) {
        console.log('token: ' + currentToken);
        sendTokenToServer(currentToken);
      } else {
        // Show permission request.
        console.log('No Instance ID token available. Request permission to generate one.');
        //TODO: notify user to grand permission
      }
    })
    .catch(function (err) {
      console.log('An error occurred while retrieving token. ', err);
      showToken('Error retrieving Instance ID token. ', err);
      setTokenSentToServer(false);
    })
    .catch(function (err) {
      console.log('Unable to get permission to notify.', err);
    });


    function sendTokenToServer(token){
      console.log('here');
      var xhr = new XMLHttpRequest();
      xhr.open("POST", '/firebase-send-token', true);
      
      xhr.setRequestHeader("Content-type", "application/json");


      xhr.onreadystatechange = function() {
        if(xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200) {
            console.log('answer');
        }
    }
    xhr.send('{ "token": "' + token +'" }'); 
    }
    
}());
