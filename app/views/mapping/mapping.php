<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Mapping Configuration</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Ensure this path points to your actual CSS file -->
</head>
<body>
    <div class="container">
        <h1>Product Mapping Configuration</h1>
        <p>Configure how your products are mapped between your platform and external systems.</p>

        <form action="/mapping/save" method="post">
            <div class="form-group">
                <label for="mappingType">Select Mapping Type:</label>
                <select name="mappingType" id="mappingType" class="form-control">
                    <option value="price_quantity">Price and Quantity Only</option>
                    <option value="full_product">Full Product</option>
                </select>
            </div>

            <!-- Example table for mapping configuration -->
            <h2>Mapping Details</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Field Name</th>
                        <th>Source Field</th>
                        <th>Destination Field</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Price</td>
                        <td><input type="text" name="priceSource" class="form-control" placeholder="Source Field for Price"></td>
                        <td><input type="text" name="priceDestination" class="form-control" placeholder="Destination Field for Price"></td>
                    </tr>
                    <tr>
                        <td>Quantity</td>
                        <td><input type="text" name="quantitySource" class="form-control" placeholder="Source Field for Quantity"></td>
                        <td><input type="text" name="quantityDestination" class="form-control" placeholder="Destination Field for Quantity"></td>
                    </tr>
                </tbody>
            </table>

            <!-- Submit button -->
            <button type="submit" class="btn btn-primary">Save Configuration</button>
        </form>
    </div>

    <script src="/assets/js/script.js"></script> <!-- Ensure this path points to your actual JavaScript file -->
</body>
</html>
