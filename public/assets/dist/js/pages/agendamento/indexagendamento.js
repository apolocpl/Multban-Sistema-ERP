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
        initialView: 'timeGridWeek',
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
        slotDuration: '00:15', // 15 minutes slots
        slotLabelInterval: '00:10', // 10 minutes intervals for time labels
        slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false }, // 24-hour format for time labels
        dateClick: function (info) {
            // let eventTitle = prompt('Enter event title:'); // Get event title from user
            // if (eventTitle) {
            //     calendar.addEvent({
            //         title: eventTitle,
            //         start: info.dateStr, // Use the clicked date as the start
            //         end: info.endStr,   // You can adjust the end time as needed
            //         allDay: false, // Or set to false for a specific time
            //         extendedProps: {
            //             description: 'teste'
            //         }
            //     });
            // }
        },
        select: function (info) {

            Pace.restart();
            // You can use the event ID to fetch more details or perform actions
            console.log('Selected from ' + info.startStr + ' to ' + info.endStr);
            $('#date').val(info.startStr.substring(0, 10));
            $('#start').val(info.startStr.substring(11, 16));
            $('#end').val(info.endStr.substring(11, 16));
            $('#btnAgendamento').attr('href', "/agendamento/inserir?date=" + info.startStr.substring(0, 10) + "&start=" + info.startStr.substring(11, 16) + "&end=" + info.endStr.substring(11, 16));



            //alert('selected ' + info.startStr + ' to ' + info.endStr);
        },
        //eventContent: function(arg) {
        // console.log('eventContent: ', arg);
        // return {

        //  backgroundColor: 'red',
        //             borderColor: arg.event.borderColor,
        //             textColor: arg.event.textColor,

        //             html: '<div class="fc-daygrid-event-dot"></div><div class="fc-event-time">'+arg.event.startStr+'</div><div class="fc-event-title">'+arg.event.title+'</div>' };
        //},
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
