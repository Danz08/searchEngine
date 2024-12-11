<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $jsonPath = storage_path('app/public/database.json');
        if (!file_exists($jsonPath)) {
            return response()->json(['error' => 'File JSON tidak ditemukan'], 404);
        }

        $json = file_get_contents($jsonPath);
        $data = json_decode($json, true);
        $keyword = strtolower(trim($request->input('keyword')));
        $results = [];
        $suggestions = [];

        foreach ($data as $letter => $entries) {
            foreach ($entries as $word => $details) {
                // Collect keyword suggestions
                if (preg_match("/^" . preg_quote($keyword, '/') . "/i", $word)) {
                    $suggestions['kata'][] = $word;
                    foreach ($details as $sentence => $content) {
                        if (preg_match("/^" . preg_quote($word, '/') . "/i", $sentence)) {
                            $suggestions['kalimat'][] = $sentence;
                        }
                    }
                }

                foreach ($details as $sentence => $content) {
                    // Ensure sentences follow SOP (subject, object, predicate)
                    $validSentence = $this->formatToSOP($sentence);

                    if (preg_match("/" . preg_quote($keyword, '/') . "/i", strtolower($validSentence))) {
                        $results[] = [
                            'title' => $validSentence,
                            'description' => $content['teks'],
                            'foto' => $content['foto'],
                            'video' => $content['video']
                        ];
                    }
                }
            }
        }

        return response()->json([
            'results' => $results,
            'suggestions' => $suggestions
        ]);
    }

    /**
     * Ensure sentences adhere to SOP format: Subject, Object, Predicate.
     *
     * @param string $sentence
     * @return string
     */
    private function formatToSOP(string $sentence): string
    {
        // Basic logic to ensure the sentence has a subject, object, and predicate
        $words = explode(' ', $sentence);
        if (count($words) < 3) {
            return "Kalimat tidak lengkap: " . $sentence;
        }

        $subject = $words[0];
        $predicate = $words[1];
        $object = implode(' ', array_slice($words, 2));

        return ucfirst(trim("$subject $predicate $object")) . '.';
    }
}

?>