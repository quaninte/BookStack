<?php namespace BookStack\Entities;

use Illuminate\Support\Str;

class SlugGenerator
{

    protected $entity;

    /**
     * SlugGenerator constructor.
     * @param $entity
     */
    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Generate a fresh slug for the given entity.
     * The slug will generated so it does not conflict within the same parent item.
     */
    public function generate(): string
    {
        $slug = $this->formatNameAsSlug($this->entity->name);
        while ($this->slugInUse($slug)) {
            $slug .= '-' . substr(md5(rand(1, 500)), 0, 3);
        }
        return $slug;
    }

    /**
     * Format a name as a url slug.
     */
    protected function formatNameAsSlug(string $name): string
    {
        $slug = preg_replace('/[\+\/\\\?\@\}\{\.\,\=\[\]\#\&\!\*\'\;\$\%]/', '', mb_strtolower($name));
        $slug = preg_replace('/\s{2,}/', ' ', $slug);

        $slug = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $slug);
        $slug = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $slug);
        $slug = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $slug);
        $slug = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $slug);
        $slug = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $slug);
        $slug = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $slug);
        $slug = preg_replace("/(đ)/", 'd', $slug);
        $slug = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $slug);
        $slug = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $slug);
        $slug = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $slug);
        $slug = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $slug);
        $slug = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $slug);
        $slug = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $slug);
        $slug = preg_replace("/(Đ)/", 'D', $slug);
        //$slug = str_replace(" ", "-", str_replace("&*#39;","",$slug));

        $slug = str_replace(' ', '-', $slug);

        if ($slug === "") {
            $slug = substr(md5(rand(1, 500)), 0, 5);
        }
        return $slug;
    }

    /**
     * Check if a slug is already in-use for this
     * type of model within the same parent.
     */
    protected function slugInUse(string $slug): bool
    {
        $query = $this->entity->newQuery()->where('slug', '=', $slug);

        if ($this->entity instanceof BookChild) {
            $query->where('book_id', '=', $this->entity->book_id);
        }

        if ($this->entity->id) {
            $query->where('id', '!=', $this->entity->id);
        }

        return $query->count() > 0;
    }
}
