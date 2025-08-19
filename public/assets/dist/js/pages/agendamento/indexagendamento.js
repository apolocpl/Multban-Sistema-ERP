$(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /* initialize the calendar
     -----------------------------------------------------------------*/

    var Calendar = FullCalendar.Calendar;

    var calendarEl = document.getElementById('calendar');

    // initialize the external events
    // -----------------------------------------------------------------

    var calendar = new Calendar(calendarEl, {
        events: {
            url: "/get-agendamento",
            type: 'POST',
            extraParams: {
                custom_param1: 'something',
                custom_param2: 'somethingelse'
            },
            error: function () {
                alert('there was an error while fetching events!');
            },
        },
        locale: 'pt-br',
        initialView: 'dayGridMonth',
        timeZone: 'UTC',
        slotMinTime: "08:00:00",
        slotMaxTime: "20:00:00",
        selectable: true,
        selectMirror: false,
        dayMaxEvents: true, // allow "more" link when too many events
        navLinks: true, // can click day/week names to navigate views
        editable: false,
        droppable: false, // this allows things to be dropped onto the calendar !!!
        displayEventTime: true,
        displayEventEnd: true,
        firstDay: 1, // Monday as the first day of the week
        slotDuration: '00:15:00', // 30 minutes slots
        //slotLabelInterval: '00:15', // 1 hour intervals for time labels
        slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false }, // 24-hour format for time labels
        //         eventContent: function(arg) {
        //             console.log('eventContent: ', arg);
        //           return {

        // backgroundColor: 'red',
        //             borderColor: arg.event.borderColor,
        //             textColor: arg.event.textColor,

        //             html: '<div class="fc-daygrid-event-dot"></div><div class="fc-event-time">'+arg.event.startStr+'</div><div class="fc-event-title">'+arg.event.title+'</div>' };
        //         },
        eventDidMount: function (info) {
            console.log('eventDidMount: ', info.event);
            new bootstrap.Tooltip(info.el, {
                title: info.event.extendedProps.description, // Or whatever content you want
                placement: 'top', // Adjust placement as needed
                trigger: 'hover', // Show on hover
                container: 'body', // Attach to the body to prevent clipping
                html: true, // Allow HTML content

                delay: { "show": 100, "hide": 100 } // Optional delay for showing/hiding
            });

            $('.fc-daygrid-event-dot').hide();
            $('.fc-list-event-graphic').hide();
        },
        eventTimeFormat: { // like '14:30:00'
            hour: '2-digit',
            minute: '2-digit',
            meridiem: false
        },
        eventMouseEnter: function (info) {
            console.log('Event: ', info.event);

            // alert('Coordinates: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY);
            // alert('View: ' + info.view.type);

            // change the border color just for fun
            info.el.style.cursor = 'pointer';
        },
        eventClick: function (info) {
            console.log('eventClick: ', info.event);
            // Show a tooltip with the event description
            // alert('Event: ' + info.event.title);
            // alert('Coordinates: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY);
            // alert('View: ' + info.view.type);
            Pace.restart();
            Pace.track(function () {
                // You can use the event ID to fetch more details or perform actions
                console.log('Event ID: ', info.event.id);
                window.location.href = "/agendamento/" + info.event.id + "/alterar";
            });

            // change the border color just for fun
            info.el.style.borderColor = 'red';
        },
        headerToolbar: {
            left: 'prev,next,today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        themeSystem: 'bootstrap',

    });

    calendar.render();
})
