<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kino Baza - Ommaviy Yuklash</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 p-4 font-sans text-gray-800">

    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden p-6">
        <h2 class="text-2xl font-bold text-center text-blue-600 mb-2">🎬 Ommaviy Kino Qo'shish Panel</h2>
        <p class="text-center text-gray-500 text-sm mb-6">Maksimal 20 tagacha kino ma'lumotlarini bir vaqtda kiriting</p>
        
        <form id="kinoForm" action="save_movie.php" method="POST" class="space-y-6">
            
            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 max-w-md mx-auto">
                <label class="block text-sm font-semibold text-yellow-800 mb-1">🔐 Admin Xavfsizlik Paroli:</label>
                <input type="password" name="admin_password" id="admin_password" required placeholder="Sayt parolini kiriting" 
                       class="block w-full px-3 py-2 bg-white border border-yellow-300 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500">
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">№</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Message ID (Kanal xabari raqami)</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Eski Start So'rovi (Payload)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <?php for($i = 1; $i <= 100; $i++): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm font-medium text-gray-400"><?= $i ?></td>
                            <td class="px-2 py-1">
                                <input type="number" name="movies[<?= $i ?>][message_id]" data-id="msg_<?= $i ?>" data-index="<?= $i ?>" placeholder="Masalan: 102" 
                                       class="msg-input save-local w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                            </td>
                            <td class="px-2 py-1">
                                <input type="text" name="movies[<?= $i ?>][file_code]" data-id="code_<?= $i ?>" data-code-index="<?= $i ?>" placeholder="Masalan: _IACIA..." 
                                       class="code-input save-local w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                            </td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-center">
                <button type="submit" 
                        class="w-full md:w-1/3 py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer">
                    💾 To'ldirilganlarni Saqlash
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Xotiradan ma'lumotlarni tiklash
            if(localStorage.getItem("admin_password")) {
                document.getElementById("admin_password").value = localStorage.getItem("admin_password");
            }

            document.querySelectorAll(".save-local").forEach(input => {
                const key = input.getAttribute("data-id");
                if (localStorage.getItem(key)) {
                    input.value = localStorage.getItem(key);
                }

                input.addEventListener("input", function() {
                    localStorage.setItem(key, this.value);
                    // Qizil chegarani olib tashlash (agar admin to'g'rilayotgan bo'lsa)
                    this.classList.remove("border-red-500", "bg-red-50");
                    this.classList.add("border-gray-300");
                });
            });

            document.getElementById("admin_password").addEventListener("input", function() {
                localStorage.setItem("admin_password", this.value);
            });

            // 2. AVTO-TO'LDIRISH MANTIQLARI
            const msgInputs = document.querySelectorAll(".msg-input");
            msgInputs.forEach(input => {
                input.addEventListener("input", function() {
                    let startValue = parseInt(this.value);
                    let currentIndex = parseInt(this.getAttribute("data-index"));

                    if (isNaN(startValue)) return;

                    for (let j = currentIndex + 1; j <= 100; j++) {
                        let nextInput = document.querySelector(`[data-index="${j}"]`);
                        if (nextInput) {
                            startValue++;
                            nextInput.value = startValue;
                            const key = nextInput.getAttribute("data-id");
                            localStorage.setItem(key, startValue);
                        }
                    }
                });
            });
        });

        // 3. DUBLIKAT KODLARNI TEKSHIRISH VA FORMANI NAZORAT QILISH
        document.getElementById("kinoForm").addEventListener("submit", function(e) {
            const codeInputs = document.querySelectorAll(".code-input");
            let seenCodes = {};
            let hasDuplicate = false;
            let duplicateIndexes = [];

            // Avval eski qizil ranglarni tozalab olamiz
            codeInputs.forEach(input => {
                input.classList.remove("border-red-500", "bg-red-50");
                input.classList.add("border-gray-300");
            });

            // Barcha to'ldirilgan start kodlarini tekshirish
            codeInputs.forEach(input => {
                let value = input.value.trim();
                let index = input.getAttribute("data-code-index");

                if (value !== "") {
                    if (seenCodes[value]) {
                        // Agar bu kod avval ko'ringan bo'lsa, demak dublikat!
                        hasDuplicate = true;
                        duplicateIndexes.push(seenCodes[value]); // Birinchi yozilgan qator raqami
                        duplicateIndexes.push(index);            // Ikkinchi takrorlangan qator raqami
                        
                        // Dublikat inputlarni qizil rangga bo'yash
                        input.classList.remove("border-gray-300");
                        input.classList.add("border-red-500", "bg-red-50");
                        
                        let firstInput = document.querySelector(`[data-code-index="${seenCodes[value]}"]`);
                        firstInput.classList.remove("border-gray-300");
                        firstInput.classList.add("border-red-500", "bg-red-50");
                    } else {
                        // Kodni xotiraga yozib qo'yamiz (qaysi qatorda kelganini eslab qolish uchun)
                        seenCodes[value] = index;
                    }
                }
            });

            // Agar dublikat topilgan bo'lsa formani to'xtatish
            if (hasDuplicate) {
                e.preventDefault(); // Sahifa save_movie.php ga o'tib ketmaydi
                
                // Takrorlangan qatorlarni tartiblab, chiroyli matn qilish (masalan: 3, 7)
                let uniqueIndexes = [...new Set(duplicateIndexes)].sort((a, b) => a - b);
                
                alert(`⚠️ Diqqat! Ro'yxatda bir xil start kodlari aniqlandi.\n\nTakrorlangan qatorlar: ${uniqueIndexes.join(", ")}-qatorlar.\n\nIltimos, qizil rangga kirgan joylarni to'g'rilab, keyin qayta urining.`);
                return false;
            }

            // Agar hammasi toza bo'lsa, xotirani tozalash va yuborish
            document.querySelectorAll(".save-local").forEach(input => {
                const key = input.getAttribute("data-id");
                localStorage.removeItem(key);
            });
        });
    </script>

</body>
</html>
