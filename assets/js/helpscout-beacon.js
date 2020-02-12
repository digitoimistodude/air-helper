/*
* @Author: Timi Wahalahti
* @Date:   2020-02-12 14:35:14
* @Last Modified by:   Timi Wahalahti
* @Last Modified time: 2020-02-12 15:26:35
*/

!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});

window.Beacon('config', {
  color: airhelperHelpscout.color,
  enableFabAnimation: false,
  text: airhelperHelpscout.translations.text,
  labels: {
    sendAMessage: airhelperHelpscout.translations.sendAMessage,
    howCanWeHelp: airhelperHelpscout.translations.howCanWeHelp,
    responseTime: airhelperHelpscout.translations.responseTime,
    continueEditing: airhelperHelpscout.translations.continueEditing,
    lastUpdated: airhelperHelpscout.translations.lastUpdated,
    you: airhelperHelpscout.translations.you,
    nameLabel: airhelperHelpscout.translations.nameLabel,
    subjectLabel: airhelperHelpscout.translations.subjectLabel,
    emailLabel: airhelperHelpscout.translations.emailLabel,
    messageLabel: airhelperHelpscout.translations.messageLabel,
    messageSubmitLabel: airhelperHelpscout.translations.messageSubmitLabel,
    next: airhelperHelpscout.translations.next,
    weAreOnIt: airhelperHelpscout.translations.weAreOnIt,
    messageConfirmationText: airhelperHelpscout.translations.messageConfirmationText,
    wereHereToHelp: airhelperHelpscout.translations.wereHereToHelp,
    whatMethodWorks: airhelperHelpscout.translations.whatMethodWorks,
    viewAndUpdateMessage: airhelperHelpscout.translations.viewAndUpdateMessage,
    previousMessages: airhelperHelpscout.translations.previousMessages,
    messageButtonLabel: airhelperHelpscout.translations.messageButtonLabel,
    noTimeToWaitAround: airhelperHelpscout.translations.noTimeToWaitAround,
    addReply: airhelperHelpscout.translations.addReply,
    addYourMessageHere: airhelperHelpscout.translations.addYourMessageHere,
    sendMessage: airhelperHelpscout.translations.sendMessage,
    received: airhelperHelpscout.translations.received,
    waitingForAnAnswer: airhelperHelpscout.translations.waitingForAnAnswer,
    previousMessageErrorText: airhelperHelpscout.translations.previousMessageErrorText,
    justNow: airhelperHelpscout.translations.justNow,
  },
});

window.Beacon('identify', {
  name: airhelperHelpscout.userName,
  email: airhelperHelpscout.userEmail,
  Site: airhelperHelpscout.site,
  'Site URL': airhelperHelpscout.siteUrl,
});

window.Beacon('prefill', {
  subject: airhelperHelpscout.translations.prefilledSubject,
});

window.Beacon('init', '7658270b-a910-4616-95f7-ef5f78767424');
