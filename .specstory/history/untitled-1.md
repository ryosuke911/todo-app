## SpecStory

## Untitled (2025/2/13 1:54:44)

_**User**_



---

_****_

   FAILED  Tests\Unit\Services\TodoServiceTest > get todos returns paginated todos                          
  Failed asserting that actual size 2 matches expected size 3.

  at tests/Unit/Services/TodoServiceTest.php:72
     68▕     {
     69▕         $result = $this->todoService->getTodos();
     70▕ 
     71▕         $this->assertEquals(10, $result->perPage());
  ➜  72▕         $this->assertCount(3, $result->items());
     73▕     }
     74▕ 
     75▕     /**
     76▕      * @test

  1   tests/Unit/Services/TodoServiceTest.php:72　エラー分析


---



---

