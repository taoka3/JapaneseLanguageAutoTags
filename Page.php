        <!-- Background Wrapper -->
        <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-[#fdfbfb] to-[#ebedee] font-['Noto_Sans_JP']">

            <!-- Main Card -->
            <div class="max-w-md w-full p-8 rounded-[2.5rem] bg-white/70 backdrop-blur-md border border-white/50 shadow-[0_20px_40px_rgba(0,0,0,0.05)] text-gray-700">

                <!-- Header -->
                <header class="text-center mb-10">
                    <h1 class="text-2xl font-bold tracking-tight text-gray-800/80">
                        JapaneseLanguageAutoTags
                    </h1>
                    <p class="text-[10px] text-gray-400 mt-2 tracking-widest uppercase">Smart Tagging System</p>
                </header>

                <form onsubmit="event.preventDefault();" class="space-y-8">
                    <!-- API Key Input -->
                    <div class="space-y-2">
                        <label for="apikey" class="text-xs font-semibold ml-4 text-gray-400 uppercase tracking-wider">API KEY</label>
                        <input
                            type="password"
                            id="apikey"
                            placeholder="キーを入力してください..."
                            class="w-full px-6 py-4 rounded-3xl bg-white/50 border-2 border-transparent outline-none text-gray-600 placeholder-gray-300 transition-all duration-300 focus:bg-white focus:border-gray-100 focus:ring-4 focus:ring-gray-50 focus:-translate-y-0.5"
                            value="<?=$ApiKey?>"
                            >
                    </div>
                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button id="setBtn" class="w-full py-4 rounded-3xl bg-gradient-to-r from-pink-50 via-blue-50 to-purple-50 text-gray-500 font-bold shadow-sm border border-white transition-all duration-500 hover:from-pink-100 hover:via-blue-100 hover:to-purple-100 hover:text-gray-700 hover:shadow-md hover:-translate-y-1 active:scale-95">
                            APIKEYを登録する
                        </button>
                    </div>
                </form>

                <!-- Footer Indicators -->
                <footer class="mt-12 text-center">
                    <div class="inline-flex space-x-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-pink-200 animate-bounce" style="animation-duration: 2s"></div>
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-200 animate-bounce" style="animation-delay: 0.3s; animation-duration: 2s"></div>
                        <div class="w-1.5 h-1.5 rounded-full bg-purple-200 animate-bounce" style="animation-delay: 0.6s; animation-duration: 2s"></div>
                    </div>
                </footer>
            </div>
        </div>
        <script>
            document.getElementById('setBtn').addEventListener('click', function() {
                let url = '<?= admin_url('admin-ajax.php') ?>';
                let frm = new FormData();
                frm.append('action', 'setJapaneseLanguageAutoTags');
                frm.append('APIKEY', document.getElementById('apikey').value);
                fetch(url, {
                    method: 'POST',
                    body: frm
                }).then(respose => {
                    if (respose.ok) {
                        alert('保存しました.リロードします');
                        location.reload();
                    }
                }).catch(e => {
                    //console.log(e);
                });
            });
        </script>