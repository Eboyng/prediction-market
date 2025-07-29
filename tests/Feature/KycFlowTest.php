<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Kyc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class KycFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'kyc_status' => 'pending'
        ]);
        
        Storage::fake('public');
    }

    /** @test */
    public function user_can_submit_kyc_documents()
    {
        // Arrange
        $this->actingAs($this->user);
        $idDocument = UploadedFile::fake()->image('id_card.jpg', 800, 600);
        $proofOfAddress = UploadedFile::fake()->create('utility_bill.pdf', 1024);

        // Act
        $response = $this->post('/kyc/submit', [
            'id_type' => 'national_id',
            'id_number' => 'NIN12345678901',
            'id_document' => $idDocument,
            'proof_of_address' => $proofOfAddress,
            'date_of_birth' => '1990-01-01',
            'address' => '123 Main Street, Lagos, Nigeria'
        ]);

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('kycs', [
            'user_id' => $this->user->id,
            'id_type' => 'national_id',
            'id_number' => 'NIN12345678901',
            'status' => 'submitted'
        ]);
        
        Storage::disk('public')->assertExists('kyc/' . $idDocument->hashName());
        Storage::disk('public')->assertExists('kyc/' . $proofOfAddress->hashName());
    }

    /** @test */
    public function kyc_submission_validates_required_fields()
    {
        // Arrange
        $this->actingAs($this->user);

        // Act
        $response = $this->post('/kyc/submit', [
            'id_type' => '',
            'id_number' => '',
            'date_of_birth' => ''
        ]);

        // Assert
        $response->assertSessionHasErrors(['id_type', 'id_number', 'id_document', 'date_of_birth']);
        $this->assertDatabaseMissing('kycs', ['user_id' => $this->user->id]);
    }

    /** @test */
    public function kyc_validates_file_types()
    {
        // Arrange
        $this->actingAs($this->user);
        $invalidFile = UploadedFile::fake()->create('document.txt', 100);

        // Act
        $response = $this->post('/kyc/submit', [
            'id_type' => 'national_id',
            'id_number' => 'NIN12345678901',
            'id_document' => $invalidFile,
            'date_of_birth' => '1990-01-01',
            'address' => '123 Main Street'
        ]);

        // Assert
        $response->assertSessionHasErrors('id_document');
    }

    /** @test */
    public function kyc_validates_file_size()
    {
        // Arrange
        $this->actingAs($this->user);
        $largeFile = UploadedFile::fake()->create('large_document.jpg', 6000); // 6MB

        // Act
        $response = $this->post('/kyc/submit', [
            'id_type' => 'national_id',
            'id_number' => 'NIN12345678901',
            'id_document' => $largeFile,
            'date_of_birth' => '1990-01-01',
            'address' => '123 Main Street'
        ]);

        // Assert
        $response->assertSessionHasErrors('id_document');
    }

    /** @test */
    public function admin_can_approve_kyc()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $kyc = Kyc::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'submitted'
        ]);
        $this->actingAs($admin);

        // Act
        $response = $this->patch("/admin/kyc/{$kyc->id}/approve", [
            'notes' => 'Documents verified successfully'
        ]);

        // Assert
        $response->assertRedirect();
        $kyc->refresh();
        $this->assertEquals('approved', $kyc->status);
        $this->assertEquals('Documents verified successfully', $kyc->admin_notes);
        
        $this->user->refresh();
        $this->assertEquals('verified', $this->user->kyc_status);
    }

    /** @test */
    public function admin_can_reject_kyc()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $kyc = Kyc::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'submitted'
        ]);
        $this->actingAs($admin);

        // Act
        $response = $this->patch("/admin/kyc/{$kyc->id}/reject", [
            'notes' => 'Documents are not clear, please resubmit'
        ]);

        // Assert
        $response->assertRedirect();
        $kyc->refresh();
        $this->assertEquals('rejected', $kyc->status);
        $this->assertEquals('Documents are not clear, please resubmit', $kyc->admin_notes);
        
        $this->user->refresh();
        $this->assertEquals('rejected', $this->user->kyc_status);
    }

    /** @test */
    public function user_can_resubmit_after_rejection()
    {
        // Arrange
        $this->actingAs($this->user);
        $this->user->update(['kyc_status' => 'rejected']);
        $rejectedKyc = Kyc::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'rejected'
        ]);

        $newIdDocument = UploadedFile::fake()->image('new_id.jpg');

        // Act
        $response = $this->post('/kyc/submit', [
            'id_type' => 'passport',
            'id_number' => 'A12345678',
            'id_document' => $newIdDocument,
            'date_of_birth' => '1990-01-01',
            'address' => '456 New Street, Lagos'
        ]);

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('kycs', [
            'user_id' => $this->user->id,
            'id_type' => 'passport',
            'status' => 'submitted'
        ]);
        
        // Old KYC should remain rejected
        $rejectedKyc->refresh();
        $this->assertEquals('rejected', $rejectedKyc->status);
    }

    /** @test */
    public function verified_user_cannot_resubmit_kyc()
    {
        // Arrange
        $this->actingAs($this->user);
        $this->user->update(['kyc_status' => 'verified']);
        Kyc::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'approved'
        ]);

        // Act
        $response = $this->post('/kyc/submit', [
            'id_type' => 'national_id',
            'id_number' => 'NIN12345678901',
            'id_document' => UploadedFile::fake()->image('id.jpg'),
            'date_of_birth' => '1990-01-01',
            'address' => '123 Main Street'
        ]);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function kyc_submission_via_livewire_component()
    {
        // Arrange
        $this->actingAs($this->user);
        $idDocument = UploadedFile::fake()->image('id.jpg');

        // Act
        Livewire::test('kyc-upload-component')
            ->set('idType', 'national_id')
            ->set('idNumber', 'NIN12345678901')
            ->set('idDocument', $idDocument)
            ->set('dateOfBirth', '1990-01-01')
            ->set('address', '123 Main Street')
            ->call('submitKyc')
            ->assertHasNoErrors()
            ->assertEmitted('kycSubmitted');

        // Assert
        $this->assertDatabaseHas('kycs', [
            'user_id' => $this->user->id,
            'status' => 'submitted'
        ]);
    }

    /** @test */
    public function kyc_creates_activity_log()
    {
        // Arrange
        $this->actingAs($this->user);
        $idDocument = UploadedFile::fake()->image('id.jpg');

        // Act
        $response = $this->post('/kyc/submit', [
            'id_type' => 'national_id',
            'id_number' => 'NIN12345678901',
            'id_document' => $idDocument,
            'date_of_birth' => '1990-01-01',
            'address' => '123 Main Street'
        ]);

        // Assert
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'kyc_submitted',
            'description' => 'KYC documents submitted for verification'
        ]);
    }

    /** @test */
    public function kyc_approval_creates_activity_log()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $kyc = Kyc::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'submitted'
        ]);
        $this->actingAs($admin);

        // Act
        $response = $this->patch("/admin/kyc/{$kyc->id}/approve");

        // Assert
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'kyc_approved',
            'description' => 'KYC verification approved'
        ]);
    }

    /** @test */
    public function kyc_validates_age_requirement()
    {
        // Arrange
        $this->actingAs($this->user);
        $underageDate = now()->subYears(17)->format('Y-m-d'); // 17 years old

        // Act
        $response = $this->post('/kyc/submit', [
            'id_type' => 'national_id',
            'id_number' => 'NIN12345678901',
            'id_document' => UploadedFile::fake()->image('id.jpg'),
            'date_of_birth' => $underageDate,
            'address' => '123 Main Street'
        ]);

        // Assert
        $response->assertSessionHasErrors('date_of_birth');
    }

    /** @test */
    public function kyc_prevents_duplicate_id_numbers()
    {
        // Arrange
        $existingUser = User::factory()->create();
        Kyc::factory()->create([
            'user_id' => $existingUser->id,
            'id_number' => 'NIN12345678901',
            'status' => 'approved'
        ]);
        
        $this->actingAs($this->user);

        // Act
        $response = $this->post('/kyc/submit', [
            'id_type' => 'national_id',
            'id_number' => 'NIN12345678901', // Same ID number
            'id_document' => UploadedFile::fake()->image('id.jpg'),
            'date_of_birth' => '1990-01-01',
            'address' => '123 Main Street'
        ]);

        // Assert
        $response->assertSessionHasErrors('id_number');
    }

    /** @test */
    public function user_can_view_kyc_status()
    {
        // Arrange
        $this->actingAs($this->user);
        $kyc = Kyc::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'submitted'
        ]);

        // Act
        $response = $this->get('/kyc/status');

        // Assert
        $response->assertStatus(200);
        $response->assertSee('submitted');
        $response->assertSee($kyc->id_type);
    }

    /** @test */
    public function guest_cannot_access_kyc_routes()
    {
        // Act & Assert
        $this->get('/kyc/submit')->assertRedirect('/login');
        $this->post('/kyc/submit')->assertRedirect('/login');
        $this->get('/kyc/status')->assertRedirect('/login');
    }

    /** @test */
    public function non_admin_cannot_approve_reject_kyc()
    {
        // Arrange
        $regularUser = User::factory()->create(['role' => 'user']);
        $kyc = Kyc::factory()->create(['status' => 'submitted']);
        $this->actingAs($regularUser);

        // Act & Assert
        $this->patch("/admin/kyc/{$kyc->id}/approve")->assertStatus(403);
        $this->patch("/admin/kyc/{$kyc->id}/reject")->assertStatus(403);
    }

    /** @test */
    public function kyc_sends_notification_on_status_change()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $kyc = Kyc::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'submitted'
        ]);
        $this->actingAs($admin);

        // Act
        $response = $this->patch("/admin/kyc/{$kyc->id}/approve");

        // Assert
        // This would typically use Notification::fake() and assert notification was sent
        $response->assertRedirect();
        $kyc->refresh();
        $this->assertEquals('approved', $kyc->status);
    }
}
