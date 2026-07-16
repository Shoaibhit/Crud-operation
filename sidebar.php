<aside class="col-12 col-md-3 col-xl-2 sidebar p-4 position-relative">
    <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <div class="<?php echo $currentPage === 'dashboard.php' ? 'mb-5' : 'mb-3'; ?>">
        <h2 class="brand text-white fw-bold mb-1">SHOAIB</h2>
        <p class="text-muted small mb-0">SHOAIB</p>
        <?php if ($currentPage === 'dashboard.php'): ?>
            <div class="mt-3 p-3 rounded-3" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.14);">
                <p class="text-white-50 mb-1" style="font-size: 0.85rem;">Welcome back</p>
                <strong class="text-white">Good to see you, Shoaib</strong>
            </div>
        <?php endif; ?>
    </div>
    <nav class="nav flex-column gap-2">
        <a href="dashboard.php" class="nav-link d-flex align-items-center"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        <a href="charts_of_accounts2.php" class="nav-link d-flex align-items-center"><i class="bi bi-credit-card me-2"></i>Charts Of Accounts</a>
        <a href="all_journal_entries.php" class="nav-link d-flex align-items-center"><i class="bi bi-journal-bookmark me-2"></i>All Journal Entries</a>
        <a href="journal_form.php" class="nav-link d-flex align-items-center"><i class="bi bi-journal-text me-2"></i>Journal Entry</a>
        <a href="accounts.php" class="nav-link d-flex align-items-center"><i class="bi bi-list-ul me-2"></i>Accounts Ledger</a>
        <a href="trial_balance.php" class="nav-link d-flex align-items-center">
    <i class="bi bi-calculator me-2"></i>Trial Balance
</a>
<a href="balance_sheet.php" class="nav-link d-flex align-items-center">
    <i class="bi bi-clipboard-data me-2"></i>Balance Sheet
</a>
         
        
        <a href="income_form.php" class="nav-link d-flex align-items-center"><i class="bi bi-wallet me-2"></i>Add Income</a>
        <a href="expence_form.php" class="nav-link d-flex align-items-center"><i class="bi bi-wallet2 me-2"></i>Add Expenses</a>
        <a href="charts_of_accounts.php" class="nav-link d-flex align-items-center"><i class="bi bi-credit-card me-2"></i>Income/Expense Categories Reports</a>
        <a href="profit_loss_details.php" class="nav-link d-flex align-items-center"><i class="bi bi-graph-up me-2"></i>Profit / Loss</a>
        <a href="income_statement.php" class="nav-link d-flex align-items-center"><i class="bi bi-file-earmark-text me-2"></i>Income Statement</a>
        <a href="debtor_creditor.php" class="nav-link d-flex align-items-center"><i class="bi bi-people me-2"></i>Debtors / Creditors</a>
        <a href="loans.php" class="nav-link d-flex align-items-center"><i class="bi bi-credit-card me-2"></i>Loans</a>
       
        
       
        
        
    </nav>
    <div class="sidebar-footer text-muted small">
        <div class="border-top pt-3 mt-4">
            <strong>Version</strong> 1.0.0
        </div>
    </div>
</aside>
