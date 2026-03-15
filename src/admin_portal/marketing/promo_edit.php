<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\marketing\promo_edit.php
require_once 'models/Campaign.php';

$campaign = new Campaign();
$campaigns = $campaign->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Promo Code · Marketing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <!-- Header with Back Button -->
        <div class="mb-6 flex items-center gap-4">
            <a href="hotelmarketing_&_promotions.php" 
               class="group flex items-center gap-2 text-slate-600 hover:text-amber-600 transition-all bg-white px-4 py-2 rounded-xl shadow-sm hover:shadow-md border border-slate-200">
                <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                <span>Back to Campaigns</span>
            </a>
            <h1 class="text-2xl font-semibold text-slate-800">Create New Promo Code</h1>
        </div>

        <!-- Promo Code Form -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <form id="promoForm" method="POST" action="api/update_promo.php" class="p-6 space-y-6">
                <!-- Campaign Selection -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <i class="fa-regular fa-layer-group text-amber-600 mr-1"></i>
                        Select Campaign
                    </label>
                    <select name="campaign_id" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                        <option value="">-- Choose a campaign --</option>
                        <?php foreach ($campaigns as $camp): ?>
                        <option value="<?php echo $camp['id']; ?>"><?php echo htmlspecialchars($camp['campaign_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Promo Code -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <i class="fa-regular fa-ticket text-amber-600 mr-1"></i>
                        Promo Code *
                    </label>
                    <div class="flex gap-2">
                        <input type="text" name="code" id="promoCode" required
                               placeholder="e.g., SUMMER20"
                               class="flex-1 px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 font-mono uppercase">
                        <button type="button" onclick="generateCode()" 
                                class="px-4 py-3 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition">
                            <i class="fa-solid fa-shuffle"></i>
                        </button>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                    <input type="text" name="description"
                           placeholder="e.g., 20% off summer rooms"
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                </div>

                <!-- Max Uses -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Maximum Uses</label>
                    <input type="number" name="max_uses" value="100"
                           min="1" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                </div>

                <!-- Active Status -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                           class="w-5 h-5 text-amber-600 border-slate-300 rounded focus:ring-amber-500">
                    <label for="is_active" class="text-sm text-slate-700">Active immediately</label>
                </div>

                <!-- Form Actions -->
                <div class="border-t border-slate-200 pt-6 flex gap-3 justify-end">
                    <a href="hotelmarketing_&_promotions.php" 
                       class="px-6 py-3 border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-amber-600 text-white px-8 py-3 rounded-xl hover:bg-amber-700 transition shadow-lg">
                        <i class="fa-regular fa-floppy-disk mr-2"></i>
                        Create Promo Code
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function generateCode() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = '';
        for (let i = 0; i < 8; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('promoCode').value = code;
    }

    document.getElementById('promoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Creating...';
        submitBtn.disabled = true;
        
        fetch('api/update_promo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = 'hotelmarketing_&_promotions.php';
            } else {
                alert('Error: ' + data.message);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            alert('Error: ' + error);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    </script>
</body>
</html>