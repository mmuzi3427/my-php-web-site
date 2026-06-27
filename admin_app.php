<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kino Baza Boshqaruvi</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 p-4 font-sans text-gray-800">

    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden p-6">
        <h2 class="text-2xl font-bold text-center text-blue-600 mb-6">🎬 Eski kinolarni bazaga qo'shish</h2>
        
        <form action="save_movie.php" method="POST" class="space-y-4">
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Kanal xabar raqami (Message ID):</label>
                <input type="number" name="message_id" required placeholder="Masalan: 125" 
                       class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <small class="text-gray-400 text-xs">Kinoning maxfiy kanaldagi Message ID raqami</small>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Eski Start so'rovi (Payload):</label>
                <input type="text" name="file_code" required placeholder="Masalan: _IACIAHGABEZIFGBJ" 
                       class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <small class="text-gray-400 text-xs">Eski konstruktordan qolgan startdan keyingi maxsus kod</small>
            </div>

            <button type="submit" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer">
                💾 Bazaga Saqlash
            </button>
        </form>
    </div>

</body>
</html>
