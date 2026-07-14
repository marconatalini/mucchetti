import "fullcalendar";
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import bootstrapPlugin from '@fullcalendar/bootstrap';
import itLocale from '@fullcalendar/core/locales/it';


document.addEventListener("DOMContentLoaded", (evt) => {
  let calendarEl = document.getElementById('staff-permit-calendar');
  let urlEvents = document.getElementById('urlEvents');

  let calendar = new Calendar(calendarEl, {
    plugins: [dayGridPlugin, interactionPlugin, bootstrapPlugin],
    initialView: 'dayGridMonth',
    locale: 'it',
    eventSources: [
      {
        // url: 'http://tennis.locale/prenotazione/json',
        url: urlEvents.getAttribute('href'),
        method: 'POST',
        failure: function() {
          alert('Errore durante la ricerca dei permessi!');
        },
      }
    ],
    eventClick: function(info) {
      if (info.event.url) {
        window.open(info.event.url, '_self');
      }
    }

  }).render();
})
