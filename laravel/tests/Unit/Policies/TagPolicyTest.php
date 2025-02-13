<?php

namespace Tests\Unit\Policies;

use App\Models\Tag;
use App\Models\User;
use App\Policies\TagPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagPolicyTest extends TestCase
{
    use RefreshDatabase;

    private TagPolicy $policy;
    private User $user;
    private Tag $tag;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TagPolicy();

        // テストユーザーの作成
        $this->user = User::factory()->create();

        // テスト用のタグを作成
        $this->tag = Tag::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'テストタグ'
        ]);
    }

    /**
     * @test
     * @group tag_policy_base
     */
    public function view_any_allows_authenticated_user()
    {
        $result = $this->policy->viewAny($this->user);

        $this->assertTrue($result);
    }

    /**
     * @test
     * @group tag_policy_base
     */
    public function view_allows_tag_owner()
    {
        $result = $this->policy->view($this->user, $this->tag);

        $this->assertTrue($result);
    }

    /**
     * @test
     * @group tag_policy_base
     */
    public function view_denies_non_owner()
    {
        // 別のユーザーを作成
        $otherUser = User::factory()->create();

        $result = $this->policy->view($otherUser, $this->tag);

        $this->assertFalse($result);
    }

    /**
     * @test
     * @group tag_policy_base
     */
    public function create_allows_authenticated_user()
    {
        $result = $this->policy->create($this->user);

        $this->assertTrue($result);
    }

    /**
     * @test
     * @group tag_policy_base
     */
    public function update_allows_tag_owner()
    {
        $result = $this->policy->update($this->user, $this->tag);

        $this->assertTrue($result);
    }

    /**
     * @test
     * @group tag_policy_base
     */
    public function update_denies_non_owner()
    {
        // 別のユーザーを作成
        $otherUser = User::factory()->create();

        $result = $this->policy->update($otherUser, $this->tag);

        $this->assertFalse($result);
    }

    /**
     * @test
     * @group tag_policy_base
     */
    public function delete_allows_tag_owner()
    {
        $result = $this->policy->delete($this->user, $this->tag);

        $this->assertTrue($result);
    }

    /**
     * @test
     * @group tag_policy_base
     */
    public function delete_denies_non_owner()
    {
        // 別のユーザーを作成
        $otherUser = User::factory()->create();

        $result = $this->policy->delete($otherUser, $this->tag);

        $this->assertFalse($result);
    }

    /**
     * @test
     * @group tag_policy_base
     */
    public function restore_is_not_allowed()
    {
        $result = $this->policy->restore($this->user, $this->tag);

        $this->assertFalse($result);
    }

    /**
     * @test
     * @group tag_policy_base
     */
    public function force_delete_is_not_allowed()
    {
        $result = $this->policy->forceDelete($this->user, $this->tag);

        $this->assertFalse($result);
    }
} 