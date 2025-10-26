<?php

declare(strict_types=1);

use App\Livewire\PettyCash\TransactionsTable;
use App\Models\PettyCashLedger;
use App\Models\PettyCashTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create admin user
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Superadmin');

    // Create regular user
    $this->user = User::factory()->create();

    // Create ledger
    $this->ledger = PettyCashLedger::factory()->create([
        'branch_name' => 'Test Branch',
        'assigned_to' => $this->user->id,
    ]);

    // Create transaction
    $this->transaction = PettyCashTransaction::factory()->create([
        'ledger_id' => $this->ledger->id,
        'status' => PettyCashTransaction::STATUS_SUBMITTED,
        'requested_by' => $this->user->id,
    ]);
});

test('admin can open approve modal for transaction', function () {
    actingAs($this->admin);

    livewire(TransactionsTable::class, ['ledger' => $this->ledger])
        ->call('requestApprove', $this->transaction->id)
        ->assertSet('showApproveModal', true)
        ->assertSet('approvalTransactionId', $this->transaction->id);
});

test('admin can open reject modal for transaction', function () {
    actingAs($this->admin);

    livewire(TransactionsTable::class, ['ledger' => $this->ledger])
        ->call('requestReject', $this->transaction->id)
        ->assertSet('showRejectModal', true)
        ->assertSet('rejectTransactionId', $this->transaction->id);
});

test('non-admin cannot approve transactions', function () {
    actingAs($this->user);

    livewire(TransactionsTable::class, ['ledger' => $this->ledger])
        ->call('requestApprove', $this->transaction->id)
        ->assertSet('showApproveModal', false)
        ->assertSessionHas('error', __('شما مجاز به تایید تراکنش‌ها نیستید.'));
});

test('non-admin cannot reject transactions', function () {
    actingAs($this->user);

    livewire(TransactionsTable::class, ['ledger' => $this->ledger])
        ->call('requestReject', $this->transaction->id)
        ->assertSet('showRejectModal', false)
        ->assertSessionHas('error', __('شما مجاز به رد تراکنش‌ها نیستید.'));
});

test('admin can approve transaction successfully', function () {
    actingAs($this->admin);

    livewire(TransactionsTable::class, ['ledger' => $this->ledger])
        ->set('approvalTransactionId', $this->transaction->id)
        ->set('approvalNote', 'Test approval note')
        ->call('approveSelectedTransaction')
        ->assertSessionHas('success', __('تراکنش با موفقیت تایید شد.'));

    expect($this->transaction->fresh()->status)->toBe(PettyCashTransaction::STATUS_APPROVED);
});

test('admin can reject transaction with reason', function () {
    actingAs($this->admin);

    livewire(TransactionsTable::class, ['ledger' => $this->ledger])
        ->set('rejectTransactionId', $this->transaction->id)
        ->set('rejectNote', 'Test rejection reason')
        ->call('rejectSelectedTransaction')
        ->assertSessionHas('success', __('تراکنش با موفقیت رد شد.'));

    expect($this->transaction->fresh()->status)->toBe(PettyCashTransaction::STATUS_REJECTED);
});

test('cannot reject transaction without reason', function () {
    actingAs($this->admin);

    livewire(TransactionsTable::class, ['ledger' => $this->ledger])
        ->set('rejectTransactionId', $this->transaction->id)
        ->set('rejectNote', '')
        ->call('rejectSelectedTransaction')
        ->assertHasErrors(['rejectNote']);
});
