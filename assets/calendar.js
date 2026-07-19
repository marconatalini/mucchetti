import "fullcalendar";
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import bootstrapPlugin from '@fullcalendar/bootstrap';
import itLocale from '@fullcalendar/core/locales/it';


document.addEventListener("DOMContentLoaded", (evt) => {
  let calendarEl = document.getElementById('staff-permit-calendar');
  let urlEvents = document.getElementById('urlEvents');

  let calendar = new Calendar(calendarEl, {
    plugins: [timeGridPlugin, dayGridPlugin, interactionPlugin, bootstrapPlugin],
    initialView: 'timeGridWeek',
    slotMinTime: '08:00:00',
    slotMaxTime: '19:00:00',
    weekends: false,
    locale: 'it',
    headerToolbar: {
      start: 'prev,next today', // <-- wire up here
      center: 'title',
      end: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
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
