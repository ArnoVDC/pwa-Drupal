(function (drupalSettings) {

  if (!('serviceWorker' in navigator)) {
    return;
  }

  const documentElement = document.documentElement;
  const width = documentElement.clientWidth;
  const height = documentElement.clientHeight;

  function loadPage(url) {
    let iframe = document.createElement('iframe');
    // When loaded remove from page.
    iframe.addEventListener('load', (event) => {
      iframe.remove();
      iframe = null;
    });
    iframe.setAttribute('width', width);
    iframe.setAttribute('height', height);
    iframe.setAttribute('style', 'position:absolute;top:-110%;left:-110%;');
    iframe.setAttribute('src', url);
    document.body.appendChild(iframe);
  }

  var sw = true;

  navigator.serviceWorker.register('/serviceworker-pwa.js', {
      scope: '/'
    })
    .then((registration) => {
      // Only add default pages to cache if the SW is being installed.
      if (registration.installing) {
        // open the pages to cache in an iframe because assets are not
        // predictable.
        drupalSettings.pwa.precache.forEach(loadPage);
      }
    })
    .catch(function (err) {
      sw = false;
    });

  // Reload page when user is back online on a fallback offline page.
  window.addEventListener('online', function () {
    const loc = window.location;
    // If the page served is the offline fallback, try a refresh when user
    // get back online.
    if (loc.pathname !== '/offline' && document.querySelector('[data-drupal-pwa-offline]')) {
      loc.reload();
    }
  });


  //code Arno
  //check if browser supports push notifications
  if ('PushManager' in window && sw) {
    //push manager is supported
    console.info('we support push notifications');
    subscribeUserToPush();
    sendSubscriptionToBackEnd();
  }

  //function installation when something happens
  //TODO: add this to a user input
  function askPermission() {
    return new Promise(function (resolve, reject) {
        const permissionResult = Notification.requestPermission(function (result) {
          resolve(result);
        });

        if (permissionResult) {
          permissionResult.then(resolve, reject);
        }
      })
      .then(function (permissionResult) {
        if (permissionResult !== 'granted') {
          throw new Error('We weren\'t granted permission.');
        }
      });
  }

  /**
   *function subscribes user to push notificaitons
   if we have not asked permission the browser will automaticly ask it for us. 
   */
  function subscribeUserToPush() {
    return navigator.serviceWorker.register('/serviceworker-pwa.js', {
        scope: '/'
      }).then(function (registration) {
        const subscribeOptions = {
          userVisibleOnly: true,
          applicationServerKey: urlBase64ToUint8Array(
            'BAiy6-VNN1s72Ye40O9RaZ_CU5n3lloJiwdgJShC2nMeDaFFo7XKw71zcXZMPe1tYGcUyRjWtM2dRgZwn86LGp8'
          )
        };

        return registration.pushManager.subscribe(subscribeOptions);
      })
      .then(function (pushSubscription) {
        console.log('Received PushSubscription: ', JSON.stringify(pushSubscription));
        return pushSubscription;
      });
  }

  function sendSubscriptionToBackEnd(subscription) {
    return fetch('/api/save-subscription/', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(subscription)
    })
    .then(function(response) {
      if (!response.ok) {
        throw new Error('Bad status code from server.');
      }
  
      return response.json();
    })
    .then(function(responseData) {
      if (!(responseData.data && responseData.data.success)) {
        throw new Error('Bad response from server.');
      }
    });
  }

  //code from https://gist.github.com/malko/ff77f0af005f684c44639e4061fa8019
  function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
      .replace(/\-/g, '+')
      .replace(/_/g, '/')
    ;
    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
  }

}(drupalSettings));
