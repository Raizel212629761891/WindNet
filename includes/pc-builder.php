<?php
include 'db_connection.php';

// Enhanced function to fetch components with correct column handling
function getComponents($category, $conn) {
    $options = "";
    
    // Map category to table and define which columns to use
    $tableMap = [
        'Case' => [
            'table' => 'cases',
            'display_columns' => ['name'] // Changed to match the actual cases table structure
        ],
        'Processor' => [
            'table' => 'processors',
            'display_columns' => ['brand', 'model']
        ],
        'Graphics Card' => [
            'table' => 'graphics_cards',
            'display_columns' => ['brand', 'model']
        ],
        'Memory' => [
            'table' => 'memory',
            'display_columns' => ['brand', 'capacity', 'type', 'speed'] // Changed to match memory table
        ],
        'Storage' => [
            'table' => 'storage',
            'display_columns' => ['brand', 'type', 'capacity'] // Changed to match storage table
        ]
    ];

    if (!isset($tableMap[$category])) {
        error_log("Invalid category requested: $category");
        return "<option value=''>Invalid Category</option>";
    }

    $table = $tableMap[$category]['table'];
    $display_columns = $tableMap[$category]['display_columns'];
    
    // Check if connection is valid
    if (!$conn || $conn->connect_error) {
        error_log("Database connection failed: " . ($conn ? $conn->connect_error : "No connection"));
        return "<option value=''>Database Connection Error</option>";
    }
    
    // Check if table exists
    $check_table = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check_table->num_rows == 0) {
        error_log("Table does not exist: $table");
        return "<option value=''>Category Not Available</option>";
    }
    
    // Verify all required columns exist
    foreach ($display_columns as $column) {
        $check_column = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if ($check_column->num_rows == 0) {
            error_log("Column $column missing in table: $table");
            return "<option value=''>Invalid Data Structure</option>";
        }
    }
    
    // Construct SELECT query dynamically based on display columns
    $columns_str = implode(', ', $display_columns);
    $sql = "SELECT id, $columns_str";
    
    // Add specific additional fields for display if needed
    if ($table == 'processors') {
        $sql .= ", cores, threads, base_clock";
    } elseif ($table == 'graphics_cards') {
        $sql .= ", vram";
    }
    
    $sql .= " FROM `$table` WHERE stock > 0 ORDER BY ";
    
    // Adjust ORDER BY clause based on table
    if ($table == 'cases') {
        $sql .= "name";
    } else {
        $sql .= "brand, " . ($table == 'processors' || $table == 'graphics_cards' ? "model" : 
                ($table == 'memory' ? "capacity" : "type"));
    }
    
    $result = $conn->query($sql);
    
    if (!$result) {
        error_log("SQL Error for $table: " . $conn->error);
        return "<option value=''>Database Error: " . htmlspecialchars($conn->error) . "</option>";
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Format the display name based on the table
            $display_text = "";
            if ($table == 'processors') {
                $display_text = $row['brand'] . " " . $row['model'] . " (" . $row['cores'] . "c/" . $row['threads'] . "t, " . $row['base_clock'] . "GHz)";
            } elseif ($table == 'gpu') {
                $display_text = $row['brand'] . " " . $row['model'] . " (" . $row['vram'] . "GB)";
            } elseif ($table == 'memory') {
                $display_text = $row['brand'] . " " . $row['capacity'] . "GB " . $row['type'] . " " . $row['speed'] . "MHz";
            } elseif ($table == 'storage') {
                $display_text = $row['brand'] . " " . $row['capacity'] . "GB " . $row['type'];
            } elseif ($table == 'cases') {
                $display_text = $row['name'];
            } else {
                // Default fallback for any other tables
                $display_text = implode(" ", array_map(function($col) use ($row) { return $row[$col]; }, $display_columns));
            }
            
            $options .= "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($display_text) . "</option>";
        }
    } else {
        error_log("No components found in table: $table");
        $options = "<option value=''>No components available</option>";
    }

    return $options;
}

// Check database connection before trying to use it
$connection_error = false;
if (!isset($conn) || $conn->connect_error) {
    error_log("Database connection failed or not established");
    $connection_error = true;
}
?>
<?php include 'includes/pcbuilder.php'; ?>

<script src="pc_tier_calculator.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php 
if (isset($conn) && !$connection_error) {
    $conn->close(); 
}
?>