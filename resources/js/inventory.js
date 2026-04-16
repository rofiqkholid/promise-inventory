/**
 * Promise Inventory Helper Functions
 * 
 * MANDATORY SYNC: If calculation logic here changes, 
 * you MUST also update App\Services\Inventory\StockCalculator.php
 */
window.InventoryHelper = {
    /**
     * Calculate Pcs from Qty based on unit and gross coil weight.
     */
    calculatePcs: function(qty, pcsPerUnit, unitCode, grossCoil = 0) {
        qty = parseFloat(qty || 0);
        pcsPerUnit = parseFloat(pcsPerUnit || 1);
        grossCoil = parseFloat(grossCoil || 0);
        unitCode = (unitCode || '').toLowerCase();

        // Ratio-based coil calculation if grossCoil > 0 and unit is KG or COIL
        if (grossCoil > 0 && (unitCode.includes('coil') || unitCode.includes('kg'))) {
            return (qty / grossCoil) * pcsPerUnit;
        }
        
        // Standard multiplication
        return qty * pcsPerUnit;
    },

    /**
     * Generate HTML display for quantities with secondary UoM (Pcs).
     */
    formatQtyHtml: function(qty, pcsPerUnit, unitCode, weightKg, prefix = '', grossCoil = 0) {
        let pcs = this.calculatePcs(qty, pcsPerUnit, unitCode, grossCoil);

        let pcsDisplay = Math.abs(pcs).toLocaleString(undefined, { maximumFractionDigits: 0 });
        let unitDisplay = Math.abs(qty).toLocaleString(undefined, { maximumFractionDigits: 2 });

        let unitLabel = (unitCode || '').toUpperCase();
        if (unitLabel.includes('COIL')) {
            unitLabel = 'KG';
        }

        // If it's a 1:1 ratio and not a coil, just show the number
        if (pcsPerUnit == 1 && !unitCode.toLowerCase().includes('coil')) {
            return `<span class='font-bold'>${prefix}${pcsDisplay}</span>`;
        }

        return `
            <div class='flex flex-col items-center justify-center'>
                <span class='font-bold text-gray-900 dark:text-white'>${prefix}${unitDisplay} ${unitLabel}</span>
                <span class='text-[10px] text-gray-400 leading-none mt-1 uppercase font-bold tracking-tighter'>${pcsDisplay} PCS</span>
            </div>`;
    }
};
