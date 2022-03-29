// Initialize Firebase - https://firebase.google.com/docs/web/setup
var config = {
    apiKey: "AIzaSyB5EU7IULTt0k-7P21OChkHcxRfh9LYCOk",
    authDomain: "zigzug-e4308.firebaseapp.com",
    databaseURL: "https://zigzug-e4308.firebaseio.com",
    projectId: "zigzug-e4308",
    storageBucket: "zigzug-e4308.appspot.com",
    messagingSenderId: "971650391395"
};
firebase.initializeApp(config);

var messaging = firebase.messaging();

messaging.usePublicVapidKey("BPunHJu5oktw_RQwzb_Il4jyPRzDK5Uwgy5Wxh0ITdiXCgFpIBCaU4i08HlWZ2TT1tk1lt6IU443COQvIWDX0R8");

messaging.requestPermission()
    .then(function() {
        return messaging.getToken();
    })
    .then(function(token) {
        // send rest call to add to database
        console.log(token);
        $.ajax('https://zigzug-e4308.firebaseio.com/pushtokens/'+token+'.json', {
            method: 'PUT',
            data: 'true',
            error: function(err) {
            }
        });
    })
    .catch(function(err) {
        console.log('Permission denied');
    });
