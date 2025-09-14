import "fullcalendar";
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import bootstrapPlugin from '@fullcalendar/bootstrap';


document.addEventListener("DOMContentLoaded", (evt) => {
  let calendarEl = document.getElementById('staff-permit-calendar');
  let urlEvents = document.getElementById('urlEvents');

  let calendar = new Calendar(calendarEl, {
    plugins: [dayGridPlugin, interactionPlugin, bootstrapPlugin],
    initialView: 'dayGridMonth',
    eventSources: [
      {
        // url: 'http://tennis.locale/prenotazione/json',
        url: urlEvents.getAttribute('href'),
        method: 'POST',
        failure: function() {
          alert('there was an error while fetching events!');
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
