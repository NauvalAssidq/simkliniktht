<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Antrian Poli THT</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind 4 via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="text-slate-600 antialiased h-screen flex overflow-hidden">

    <!-- PUBLIC DISPLAY TV VIEW -->
    <div class="w-full h-full flex flex-col md:flex-row overflow-hidden relative">
        <!-- Background Flat Blue -->
        <div class="absolute inset-0 bg-primary-600 z-0"></div>

        <!-- Main Call Area -->
        <div class="flex-[2] flex flex-col items-center justify-center p-12 z-10 text-center text-white relative border-r border-white/20">
            <div class="uppercase tracking-[0.2em] font-medium text-xl mb-8 opacity-80">Sedang Dipanggil</div>

            <div id="current-number" class="bg-white text-primary-600 font-bold px-16 py-8 rounded-3xl text-[12rem] leading-none shadow-none mb-12 transition-all duration-300 transform scale-100">
                --
            </div>

            <div id="current-poli" class="text-2xl font-semibold bg-primary-700/50 px-8 py-3 rounded-full border border-primary-500">
                Poli THT 01
            </div>
        </div>

        <!-- Side List -->
        <div class="flex-1 bg-white z-10 p-8 flex flex-col">
            <div class="text-slate-400 uppercase tracking-widest font-bold border-b border-neutral-200 pb-6 mb-8 text-center text-lg">
                Antrian Selanjutnya
            </div>

            <div id="waiting-list" class="flex-1 space-y-4">
                <!-- Waiting list items will be injected here -->
                <div class="animate-pulse bg-neutral-50 p-6 rounded-xl h-24 border border-neutral-200"></div>
                <div class="animate-pulse bg-white p-6 rounded-xl h-24 border border-neutral-200 opacity-60"></div>
            </div>

            <div class="mt-auto text-center border-t border-neutral-200 pt-8">
                <h2 class="text-5xl font-light text-slate-800" id="clock">--:--</h2>
                <p class="text-slate-400 mt-2 font-medium">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
    </div>

    <script>
        let lastNumber = '';

        function updateClock() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
        }
        setInterval(updateClock, 1000);
        updateClock();

        function updateQueue() {
            $.ajax({
                url: '/antrian/data',
                success: function(data) {
                    // Update Current
                    if (data.current) {
                        let fullNum = data.current.no_antrian + data.current.angka_antrian;

                        $('#current-number').text(fullNum);

                        // Animation if changed
                        if (fullNum !== lastNumber) {
                            lastNumber = fullNum;
                            $('#current-number').removeClass('scale-100').addClass('scale-110');
                            setTimeout(() => {
                                $('#current-number').removeClass('scale-110').addClass('scale-100');
                            }, 300);
                        }
                    } else {
                        $('#current-number').text('--');
                    }

                    // Update Waiting List
                    let listHtml = '';
                    if (data.waiting.length > 0) {
                        data.waiting.slice(0, 3).forEach(function(item, index) {
                            let fullNum = item.no_antrian + item.angka_antrian;
                            
                            // Exact style mapping to demo
                            if (index === 0) {
                                // First item: Highlighted style
                                listHtml += `
                                <div class="bg-neutral-50 p-6 rounded-xl flex items-center justify-between border border-neutral-200">
                                    <span class="text-4xl font-bold text-slate-800">${fullNum}</span>
                                    <span class="bg-yellow-100 text-yellow-700 border border-yellow-200 font-bold px-3 py-1 rounded text-xs uppercase tracking-wide">Menunggu</span>
                                </div>`;
                            } else {
                                // Other items: White/faded style
                                listHtml += `
                                <div class="bg-white p-6 rounded-xl flex items-center justify-between border border-neutral-200 opacity-60">
                                    <span class="text-4xl font-bold text-slate-700">${fullNum}</span>
                                    <span class="bg-neutral-100 text-slate-500 border border-neutral-200 font-bold px-3 py-1 rounded text-xs uppercase tracking-wide">Menunggu</span>
                                </div>`;
                            }
                        });
                    } else {
                        listHtml = `
                            <div class="h-full flex flex-col items-center justify-center text-slate-300 opacity-50 space-y-4">
                                <span class="text-xl font-medium">Tidak ada antrian</span>
                            </div>
                        `;
                    }
                    $('#waiting-list').html(listHtml);
                }
            });
        }

        setInterval(updateQueue, 3000);
        updateQueue();
    </script>
</body>
</html>
