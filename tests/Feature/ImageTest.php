<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
        $this->actingAs(User::factory()->create());
    }

    public function test_an_image_can_be_uploaded(): void
    {
        $this->post(route('imagenes.store'), ['imagen' => UploadedFile::fake()->image('ryu.png'), 'nombre' => 'Ryu'])
            ->assertRedirect(route('imagenes.index'))
            ->assertSessionHas('success');

        $image = Image::firstOrFail();
        $this->assertSame('Ryu', $image->nombre);
        Storage::disk('uploads')->assertExists($image->ruta);
    }

    public function test_only_images_up_to_10mb_are_accepted(): void
    {
        $this->post(route('imagenes.store'), ['imagen' => UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('imagen');

        $this->post(route('imagenes.store'), ['imagen' => UploadedFile::fake()->image('grande.png')->size(11000)])
            ->assertSessionHasErrors('imagen');

        $this->assertDatabaseCount('images', 0);
    }

    public function test_gallery_lists_images(): void
    {
        Image::factory()->create(['nombre' => 'Chun-Li']);

        $this->get(route('imagenes.index'))->assertOk()->assertSee('Chun-Li');
    }

    public function test_deleting_an_image_removes_the_file_and_unsets_players(): void
    {
        $this->post(route('imagenes.store'), ['imagen' => UploadedFile::fake()->image('ken.png')]);
        $image = Image::firstOrFail();
        $player = Player::factory()->create(['image_id' => $image->id]);

        $this->delete(route('imagenes.destroy', $image))->assertRedirect(route('imagenes.index'));

        Storage::disk('uploads')->assertMissing($image->ruta);
        $this->assertNull($player->fresh()->image_id);
    }

    public function test_a_player_can_choose_an_image(): void
    {
        $image = Image::factory()->create();
        $player = Player::factory()->create();

        $this->put(route('players.update', $player), ['nombre' => $player->nombre, 'image_id' => $image->id])
            ->assertSessionHasNoErrors();
        $this->assertSame($image->id, $player->fresh()->image_id);

        $this->put(route('players.update', $player), ['nombre' => $player->nombre, 'image_id' => ''])
            ->assertSessionHasNoErrors();
        $this->assertNull($player->fresh()->image_id);

        $this->put(route('players.update', $player), ['nombre' => $player->nombre, 'image_id' => 999])
            ->assertSessionHasErrors('image_id');
    }

    public function test_players_page_shows_the_image_picker(): void
    {
        $image = Image::factory()->create(['nombre' => 'Guile']);
        Player::factory()->create(['image_id' => $image->id]);

        $this->get(route('players.index'))->assertOk()->assertSee('name="image_id"', false)->assertSee($image->url, false);
    }
}
