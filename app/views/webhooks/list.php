<?php $userId = $_SESSION['user_id'] ?? null; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shopify logs List</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <style>
    /* Responsive table */
    .table-responsive {
      overflow-x: auto;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 1em;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: left;
    }
    th {
      background-color: #f4f4f4;
    }
    /* Pagination styling */
    .pagination a, .pagination strong {
      margin: 0 5px;
      text-decoration: none;
    }
    /* Modal styling */
    .modal {
      display: none; /* Hidden by default */
      position: fixed;
      z-index: 999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.5);
      padding-top: 60px;
    }
    .modal-content {
      background-color: #fff;
      margin: 5% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 80%;
      max-width: 600px;
      position: relative;
    }
    .modal-content h2 {
      margin-top: 0;
    }
    .close-modal {
      color: #aaa;
      position: absolute;
      right: 15px;
      top: 10px;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }
    .close-modal:hover,
    .close-modal:focus {
      color: #000;
    }
    /* Button styling */
    .view-btn {
      padding: 4px 8px;
      font-size: 0.9em;
      cursor: pointer;
    }
    #modal-text { 
        overflow-wrap: anywhere !important;
    }
  </style>
</head>
<body>
  <nav>
    <ul class="navbar">
      <li><a href="/profile">Profile</a></li>
      <li><a href="/settings/shopify">Shopify Settings</a></li>
      <li><a href="/webhook_list">Logs</a></li>
      <li><a href="/settings/netsuite">NetSuite Settings</a></li>
      <li><a href="/logout">Logout</a></li>
    </ul>
  </nav>
  <div class="container">
    <h1>Webhook List</h1>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Topic</th>
            <th>Data</th>
            <th>Logs</th>
            <th>Created At</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($webhooks)) : ?>
            <?php foreach ($webhooks as $webhook) : ?>
              <tr>
                <td><?= htmlspecialchars($webhook['id']) ?></td>
                <td><?= htmlspecialchars($webhook['topic']) ?></td>
                <td>
                  <?php 
                    $dataContent = $webhook['data'];
                    if (strlen($dataContent) > 100) {
                      echo htmlspecialchars(substr($dataContent, 0, 100)) . '... ';
                      echo '<button class="view-btn" data-title="Data" data-content="' . htmlspecialchars($dataContent) . '">View</button>';
                    } else {
                      echo htmlspecialchars($dataContent);
                    }
                  ?>
                </td>
                <td>
                  <?php 
                    $logsContent = $webhook['logs'];
                    if (strlen($logsContent) > 100) {
                      echo htmlspecialchars(substr($logsContent, 0, 100)) . '... ';
                      echo '<button class="view-btn" data-title="Logs" data-content="' . nl2br(htmlspecialchars($logsContent)) . '">View</button>';
                    } else {
                      echo nl2br(htmlspecialchars($logsContent));
                    }
                  ?>
                </td>
                <td><?= htmlspecialchars($webhook['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else : ?>
            <tr>
              <td colspan="5">No webhooks found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination Links -->
    <div class="pagination">
      <?php 
        $totalPages = ceil($total / $perPage);
        for ($i = 1; $i <= $totalPages; $i++) :
          if ($i == $currentPage) :
      ?>
            <strong><?= $i ?></strong>
          <?php else : ?>
            <a href="/webhook_list?page=<?= $i ?>"><?= $i ?></a>
          <?php endif; 
        endfor; 
      ?>
    </div>
  </div>

  <!-- Modal Structure -->
  <div id="modal" class="modal">
    <div class="modal-content">
      <span class="close-modal">&times;</span>
      <h2 id="modal-title"></h2>
      <p id="modal-text"></p>
    </div>
  </div>

  <script>
    // When a view button is clicked, open the modal and set the content
    document.addEventListener('DOMContentLoaded', function () {
      const modal = document.getElementById('modal');
      const modalTitle = document.getElementById('modal-title');
      const modalText = document.getElementById('modal-text');
      const closeModal = document.querySelector('.close-modal');
      
        document.querySelectorAll('.view-btn').forEach(function(button) {
          button.addEventListener('click', function() {
            const title = button.getAttribute('data-title');
            const content = button.getAttribute('data-content');
            modalTitle.textContent = title + ' Details';
            modalText.innerHTML = content; // Use innerHTML to render <br /> tags as line breaks
            modal.style.display = 'block';
          });
        });
      
      // Close the modal when the user clicks on the close button or outside the modal content
      closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
      });
      
      window.addEventListener('click', function(event) {
        if (event.target === modal) {
          modal.style.display = 'none';
        }
      });
    });
  </script>
  <script src="/assets/js/script.js"></script>
</body>
</html>
