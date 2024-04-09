<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Content;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response as res;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class AboutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $section = Section::where('slug', '=',request()->segment(2))->first();

        $identitas = Content::where('section_id','=',$section->id)->whereNull('deleted_at')->first();
        if($identitas) {
            $data['model'] = Content::find($identitas->id);
            $data['content'] = json_decode($data['model']->content);
        } else {
            $data['model'] = new Content();
        }
        return view('back.about.form', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model = new Content();
        return view('back.about.form', ['model' => $model]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ],
        [
            'title.required' => 'Judul Konten Wajib Diisi',
        ]);

        $content = $request->content;

        if(isset($content)) {
            $dom = new \DomDocument();
            $dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $imageFile = $dom->getElementsByTagName('imageFile');
        
            foreach($imageFile as $item => $image){
                $data = $content->getAttribute('src');
                list($type, $data) = explode(';', $data);
                list(, $data)      = explode(',', $data);
                $imgeData = base64_decode($data);
                $image_name= "/upload/" . time().$item.'.png';
                $path = public_path() . $image_name;
                file_put_contents($path, $imgeData);
                
                $image->removeAttribute('src');
                $image->setAttribute('src', $image_name);
            }
            $content = $dom->saveHTML();
        }

        $model = new Content();

        $model->title = $request->title;
        $model->subtitle = $request->subtitle;
        $model->slug = $this->textToSlug($request->title);
        $model->short_description = $request->short_description;
        $model->content = $content;
        $model->section_id = 5;
        $model->active = 1;
        $model->save();

        if($request->file('gambar')) {
            $file_name = time().'-'.Auth::user()->id.'-about-'.$request->file('gambar')->hashName();
            $request->file('gambar')->move(public_path('frontend/assets/img'), $file_name);

            $asset = new Asset();
            $asset->thumbnail = $file_name;
            $asset->content_id = $model->id;
            $asset->keterangan = "thumbnail";
            $asset->save();
        }

        if ($model->save()) {
            return redirect()->route('administrator.about.index')->with('alert.success', 'About Berhasil Tersimpan');
        } else {
            return redirect()->route('administrator.about.create')->with('alert.failed', 'Something Wrong');
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model = Content::find($id);
        return res::json($model);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $id = base64_decode($id);
        $model = Content::find($id);
        return view('back.about.form', ['model' => $model]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
        ],
        [
            'title.required' => 'Title About Wajib Diisi',
        ]);

        $content = $request->content;

        if(isset($content)) {
            $dom = new \DomDocument();
            $dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $imageFile = $dom->getElementsByTagName('imageFile');
        
            foreach($imageFile as $item => $image){
                $data = $content->getAttribute('src');
                list($type, $data) = explode(';', $data);
                list(, $data)      = explode(',', $data);
                $imgeData = base64_decode($data);
                $image_name= "/upload/" . time().$item.'.png';
                $path = public_path() . $image_name;
                file_put_contents($path, $imgeData);
                
                $image->removeAttribute('src');
                $image->setAttribute('src', $image_name);
            }
            $content = $dom->saveHTML();
        }

        $id = base64_decode($id);
        $model = Content::find($id);
        $asset = Asset::where('content_id',$model->id)->first();

        if($request->file('gambar')) {
            $fileName = time().'-'.Auth::user()->id.'-about-'.$request->file('gambar')->hashName();
            $request->file('gambar')->move(public_path('frontend/assets/img'), $fileName);
            
            if($asset) {
                if(file_exists(public_path('frontend/assets/img/'.$asset->thumbnail))) {
                    unlink(public_path('frontend/assets/img/'.$asset->thumbnail));
                }

                $asset_model = Asset::find($asset->id);
                $asset_model->thumbnail = $fileName;
                $asset_model->content_id = $model->id;
                $asset_model->save();
            } else {
                $asset_model = new Asset();
                $asset_model->thumbnail = $fileName;
                $asset_model->content_id = $model->id;
                $asset_model->keterangan = "thumbnail";
                $asset_model->save();
            }
        }

        $model->title = $request->title;
        $model->subtitle = $request->subtitle;
        $model->slug = $this->textToSlug($request->title);
        $model->short_description = $request->short_description;
        $model->content = $content;
        $model->section_id = 5;

        if ($model->save()) {
            return redirect()->route('administrator.about.index')->with('alert.success', 'About telah Diperbaharui');
        } else {
            return redirect()->route('administrator.about.create')->with('alert.failed', 'Something Wrong');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $id = base64_decode($id);
        $model = Content::find($id);

        $model->deleted_at = date('Y-m-d H:i:s');
        $model->deleted_by = Auth::user()->id;
        $model->save();

        $assets = Asset::where('content_id',$model->id)->get();
        foreach($assets as $asset) {
            if(file_exists(public_path('front/assets/img/'.$asset->thumbnail))) {
                unlink(public_path('front/assets/img/'.$asset->thumbnail));
            }

            $as = Asset::find($asset->id);
            $as->deleted_at = date('Y-m-d H:i:s');
            $as->deleted_by = Auth::user()->id;
            $as->save();
        }
        
    }

    public function datatable(Request $request)
    {
        $section = Section::where('slug',request()->segment(2))->first();

        $query = Content::where('section_id', $section->id)->orderBy('id','asc');
        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                $string = '<div class="btn-group">';
                $string .= '<a href="' . route('administrator.about.edit', ['id' => base64_encode($model->id)]) . '" type="button"  class="btn btn-sm btn-info" title="Edit About"><i class="fas fa-edit"></i></a>';
                $string .= '&nbsp;&nbsp;<a href="' . route('administrator.about.destroy', ['id' => base64_encode($model->id)]) . '" type="button" class="btn btn-sm btn-danger btn-delete" title="Hapus About"><i class="fa fa-trash"></i></a>';
                $string .= '</div>';
                return $string;
            })
            ->addColumn('thumb', function($model) {
                $asset = Asset::where('content_id',$model->id)->where('keterangan','thumbnail')->first();
                $thumbnail = '<img src="'.asset('front/assets/img/'.$asset->thumbnail).'" width="200px" height="75px">';
                return $thumbnail;

            })
            ->addIndexColumn()
            ->rawColumns(['action','thumb'])
            ->make(true);
    }

    private function textToSlug($text='') {
        $text = trim($text);
        if (empty($text)) return '';
          $text = preg_replace("/[^a-zA-Z0-9\-\s]+/", "", $text);
          $text = strtolower(trim($text));
          $text = str_replace(' ', '-', $text);
          $text = $text_ori = preg_replace('/\-{2,}/', '-', $text);
          return $text;
    }
}
