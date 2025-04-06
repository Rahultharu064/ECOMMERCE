<form action="https://uat.esewa.com.np/epay/main" method="POST">
    <input value="100" name="tAmt" type="hidden">
    <input value="100" name="amt" type="hidden">
    <input value="0" name="txAmt" type="hidden">
    <input value="0" name="psc" type="hidden">
    <input value="0" name="pdc" type="hidden">
    <input value="EPAYTEST" name="scd" type="hidden">
    <input value="1000" name="pid" type="hidden">
    <input value="https://localhost:7004/Mycarts/Success?q=su" type="hidden" name="su">
    <input value="https://localhost:7004/Mycarts/Failure?q=fu" type="hidden" name="fu">
    <input value="e-Sewa" class="btn btn-success" type="submit">
</form>








<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.min.js"></script>
    <h2>eSewa Payment Integration</h2>
    <form id="esewaForm" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST" onsubmit="generateSignature()">   
        <input type="text" id="total_amount" name="total_amount" value="110" required><br>       
        <input type="text" id="transaction_uuid" name="transaction_uuid" value="24155622588" required><br>       
        <input type="text" id="product_code" name="product_code" value="EPAYTEST" required><br>        
        <input type="text" id="amount" name="amount" value="100" required><br>       
        <input type="text" id="tax_amount" name="tax_amount" value="10" required><br>      
        <input type="text" id="product_service_charge" name="product_service_charge" value="0" required><br>        
        <input type="text" id="product_delivery_charge" name="product_delivery_charge" value="0" required><br>       
        <input type="text" id="success_url" name="success_url" value="https://developer.esewa.com.np/success" required><br>        
        <input type="text" id="failure_url" name="failure_url" value="https://developer.esewa.com.np/failure" required><br>       
        <input type="text" id="signed_field_names" name="signed_field_names" value="total_amount,transaction_uuid,product_code" required><br>        
        <input type="text" id="signature" name="signature" hidden readonly required><br>      
        <button type="submit">Submit Payment</button>
    </form>
    <script>
        function generateSignature() {
            var secretKey = "8gBm/:&EnhH.1/q"; 

            var signedFieldNames = "total_amount,transaction_uuid,product_code";
            var data = {
                total_amount: document.getElementById("total_amount").value.trim(),
                transaction_uuid: document.getElementById("transaction_uuid").value.trim(),
                product_code: document.getElementById("product_code").value.trim()
            };

           
            var signedData = signedFieldNames.split(",")
                .map(key => key + "=" + encodeURIComponent(data[key]))
                .join(",");
           
            var hash = CryptoJS.HmacSHA256(signedData, secretKey);
            var signature = CryptoJS.enc.Base64.stringify(hash);
            document.getElementById("signature").value = signature;
            console.log("Generated Signature:", signature);
        }
    </script>