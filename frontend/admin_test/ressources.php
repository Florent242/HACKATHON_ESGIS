<?php include 'includes/admin_test/header.php'; ?>

<div class="page-header">
    <h2 class="page-title">Gestion des Ressources</h2>
    <button class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Nouvelle Ressource
    </button>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Type</th>
                <th>Auteur</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Introduction à React</td>
                <td><span class="badge badge-blue">Workshop</span></td>
                <td>John Doe</td>
                <td>01/03/2024</td>
                <td>
                    <button class="btn btn-icon-only">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php include 'includes/admin_test/footer.php'; ?>
