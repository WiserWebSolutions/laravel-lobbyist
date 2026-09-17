<?php

namespace WiserWebSolutions\Lobbyist\Tests\Data;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use WiserWebSolutions\Lobbyist\Data\Bill;
use WiserWebSolutions\Lobbyist\Data\BillText;
use WiserWebSolutions\Lobbyist\Data\BillTextCollection;
use WiserWebSolutions\Lobbyist\Data\Legislator;
use WiserWebSolutions\Lobbyist\Data\Session;
use WiserWebSolutions\Lobbyist\Data\Vote;
use WiserWebSolutions\Lobbyist\Enums\Chamber;
use WiserWebSolutions\Lobbyist\Enums\Party;
use WiserWebSolutions\Lobbyist\Enums\StateEnum;
use WiserWebSolutions\Lobbyist\Exceptions\LobbyistException;
use WiserWebSolutions\Lobbyist\Tests\TestCase;

/**
 * Core DTOs are provider-agnostic: they derive typed properties from a
 * normalized `meta` shape. Provider-specific mapping is tested in each driver
 * package's mapper tests.
 */
class DtoMappingTest extends TestCase
{
    public function test_session_derives_from_normalized_meta(): void
    {
        $session = new Session(meta: [
            'id' => 1791,
            'state' => StateEnum::CA,
            'title' => '2021-2022 Regular Session',
            'name' => '2021-2022 Session',
        ]);

        $this->assertSame(1791, $session->id);
        $this->assertSame(StateEnum::CA, $session->state);
        $this->assertSame('2021-2022 Regular Session', $session->title);
    }

    public function test_bill_derives_from_normalized_meta(): void
    {
        $bill = new Bill(meta: [
            'id' => 1132030,
            'number' => 'AB1',
            'title' => 'Youth athletics',
            'state' => StateEnum::CA,
            'last_action_date' => '2018-12-04',
        ]);

        $this->assertSame(1132030, $bill->id);
        $this->assertSame('AB1', $bill->number);
        $this->assertSame(StateEnum::CA, $bill->state);
        $this->assertSame(Chamber::House, $bill->chamber); // inferred from "A" prefix
        $this->assertSame('2018-12-04', $bill->lastActionDate?->format('Y-m-d'));
    }

    public function test_bill_is_null_safe_on_empty_meta(): void
    {
        $bill = new Bill(meta: []);

        $this->assertSame(0, $bill->id);
        $this->assertSame('', $bill->number);
        $this->assertSame(StateEnum::US, $bill->state);
        $this->assertNull($bill->statusDate);
        $this->assertNull($bill->lastActionDate);
    }

    public function test_bill_chamber_prefers_explicit_over_inference(): void
    {
        $bill = new Bill(meta: ['number' => 'HB1', 'chamber' => Chamber::Senate]);

        $this->assertSame(Chamber::Senate, $bill->chamber);
    }

    public function test_bill_texts_returns_the_mapped_version_collection(): void
    {
        $bill = new Bill(meta: [
            'number' => 'HB1',
            'texts' => [
                new BillText(meta: ['id' => 1, 'type' => 'Introduced', 'date' => '2024-01-01']),
                new BillText(meta: ['id' => 2, 'type' => 'Enrolled', 'date' => '2024-03-01']),
            ],
        ]);

        $this->assertCount(2, $bill->texts());
        $this->assertContainsOnlyInstancesOf(BillText::class, $bill->texts());
    }

    public function test_bill_text_returns_the_most_recent_version(): void
    {
        $bill = new Bill(meta: [
            'number' => 'HB1',
            'texts' => [
                new BillText(meta: ['id' => 1, 'type' => 'Introduced', 'date' => '2024-01-01', 'mime' => 'text/html', 'url' => 'https://example.test/introduced']),
                new BillText(meta: ['id' => 2, 'type' => 'Enrolled', 'date' => '2024-03-01', 'mime' => 'text/html', 'url' => 'https://example.test/enrolled']),
            ],
        ]);

        $this->assertSame(2, $bill->text()->id);
        $this->assertSame('https://example.test/enrolled', $bill->text()->toHTML());
    }

    public function test_bill_text_falls_back_to_an_unsupported_version_when_none_are_mapped(): void
    {
        $bill = new Bill(meta: ['number' => 'HB1']);

        $this->assertCount(0, $bill->texts());

        $this->expectException(LobbyistException::class);
        $this->expectExceptionMessage('Bill [HB1] does not have bill text support (toHTML()).');

        $bill->text()->toHTML();
    }

    public function test_bill_text_to_pdf_throws_when_the_only_link_is_html(): void
    {
        $text = new BillText(meta: ['id' => 1, 'bill_id' => 'HB1', 'mime' => 'text/html', 'url' => 'https://example.test/HB1']);

        $this->assertSame('https://example.test/HB1', $text->toHTML());

        $this->expectException(LobbyistException::class);
        $this->expectExceptionMessage('Bill [HB1] does not have bill text support (toPDF()).');

        $text->toPDF();
    }

    public function test_bill_text_to_string_throws_without_fetched_content(): void
    {
        $text = new BillText(meta: ['id' => 1, 'bill_id' => 'HB1']);

        $this->expectException(LobbyistException::class);
        $this->expectExceptionMessage('Bill [HB1] does not have bill text support (toString()).');

        (string) $text;
    }

    public function test_bill_text_to_string_returns_fetched_content(): void
    {
        $text = new BillText(meta: ['id' => 1, 'content' => 'the bill text']);

        $this->assertSame('the bill text', (string) $text);
        $this->assertSame('the bill text', $text->toString());
    }

    public function test_vote_derives_from_normalized_meta(): void
    {
        $vote = new Vote(meta: [
            'id' => 55,
            'bill_id' => 1132030,
            'chamber' => Chamber::House,
            'date' => '2019-05-01',
            'description' => 'Third Reading',
            'yea' => 60,
            'nay' => 15,
            'nv' => 2,
            'absent' => 3,
            'passed' => true,
        ]);

        $this->assertSame(55, $vote->id);
        $this->assertSame(1132030, $vote->billId);
        $this->assertSame(Chamber::House, $vote->chamber);
        $this->assertSame(60, $vote->yea);
        $this->assertTrue($vote->passed);
    }

    public function test_legislator_derives_from_normalized_meta(): void
    {
        $legislator = new Legislator(meta: [
            'id' => 9001,
            'name' => 'Jane Doe',
            'party' => Party::Democrat,
            'chamber' => Chamber::House,
            'district' => 'HD-042',
            'state' => StateEnum::PA,
        ]);

        $this->assertSame(9001, $legislator->id);
        $this->assertSame('Jane Doe', $legislator->name);
        $this->assertSame(Party::Democrat, $legislator->party);
        $this->assertSame(Chamber::House, $legislator->chamber);
        $this->assertSame('HD-042', $legislator->district);
        $this->assertSame(StateEnum::PA, $legislator->state);
    }

    public function test_legislator_has_no_addresses_by_default(): void
    {
        $legislator = new Legislator(meta: ['id' => 1]);

        $this->assertNull($legislator->capitolAddress);
        $this->assertNull($legislator->districtAddress);
    }

    public function test_legislator_derives_addresses_from_normalized_meta(): void
    {
        $legislator = new Legislator(meta: [
            'id' => 1,
            'capitol_address' => [
                'street1' => '1 Capitol Way',
                'street2' => 'Suite 100',
                'city_state_zip' => 'Harrisburg, PA 17120',
            ],
            'district_address' => [
                'street1' => '2 Main St',
                'street2' => '',
                'city_state_zip' => 'Erie, PA 16501',
            ],
        ]);

        $this->assertSame('1 Capitol Way', $legislator->capitolAddress->street1);
        $this->assertSame('Suite 100', $legislator->capitolAddress->street2);
        $this->assertSame('Harrisburg, PA 17120', $legislator->capitolAddress->cityStateZip);

        $this->assertSame('2 Main St', $legislator->districtAddress->street1);
        $this->assertNull($legislator->districtAddress->street2);
    }

    public function test_bill_text_derives_from_normalized_meta(): void
    {
        $text = new BillText(meta: [
            'id' => 2029,
            'bill_id' => 1132030,
            'type' => 'Introduced',
            'mime' => 'text/html',
            'date' => '2018-01-05',
            'url' => 'https://legiscan.com/CA/text/AB1/id/2029',
        ]);

        $this->assertSame(2029, $text->id);
        $this->assertSame(1132030, $text->billId);
        $this->assertSame('Introduced', $text->type);
        $this->assertSame('2018-01-05', $text->date?->format('Y-m-d'));
        $this->assertNull($text->content);
    }

    public function test_bill_text_is_null_safe_on_empty_meta(): void
    {
        $text = new BillText(meta: []);

        $this->assertSame(0, $text->id);
        $this->assertNull($text->billId);
        $this->assertSame('', $text->type);
        $this->assertNull($text->date);
    }

    public function test_bill_text_collection_latest_prefers_the_most_recent_date(): void
    {
        $texts = new BillTextCollection([
            new BillText(meta: ['id' => 1, 'type' => 'Introduced', 'date' => '2024-01-01']),
            new BillText(meta: ['id' => 2, 'type' => 'Enrolled', 'date' => '2024-03-01']),
            new BillText(meta: ['id' => 3, 'type' => 'Amended', 'date' => '2024-02-01']),
        ]);

        $this->assertSame(2, $texts->latest()->id);
    }

    public function test_bill_text_collection_latest_falls_back_to_the_last_entry_without_dates(): void
    {
        $texts = new BillTextCollection([
            new BillText(meta: ['id' => 1, 'type' => 'Printer 1']),
            new BillText(meta: ['id' => 2, 'type' => 'Printer 2']),
        ]);

        $this->assertSame(2, $texts->latest()->id);
    }

    public function test_bill_text_collection_latest_is_null_when_empty(): void
    {
        $this->assertNull((new BillTextCollection([]))->latest());
    }

    public function test_string_enums_are_resolved_by_constructor(): void
    {
        // A driver may pass a raw string; the DTO coerces it via the enum.
        $legislator = new Legislator(meta: ['party' => 'R', 'chamber' => 'senate']);

        $this->assertSame(Party::Republican, $legislator->party);
        $this->assertSame(Chamber::Senate, $legislator->chamber);
    }

    public function test_legislator_image_is_null_without_an_image_url(): void
    {
        $legislator = new Legislator(meta: ['id' => 1081]);

        $this->assertNull($legislator->image());
    }

    public function test_legislator_image_downloads_and_caches_to_the_configured_disk(): void
    {
        Storage::fake('local');
        Http::fake(['*' => Http::response('fake-jpeg-bytes', 200)]);

        $legislator = new Legislator(meta: [
            'id' => 1081,
            'image_url' => 'https://www.palegis.us/resources/images/members/200/1081.jpg',
        ]);

        $file = $legislator->image();

        $this->assertInstanceOf(File::class, $file);
        $this->assertSame('fake-jpeg-bytes', file_get_contents($file->getPathname()));
        Storage::disk('local')->assertExists('lobbyist/legislators/1081.jpg');
        Http::assertSentCount(1);

        // A second call reuses the cached file instead of downloading again.
        $legislator->image();
        Http::assertSentCount(1);
    }

    public function test_legislator_image_is_null_when_the_download_fails(): void
    {
        Storage::fake('local');
        Http::fake(['*' => Http::response('not found', 404)]);

        $legislator = new Legislator(meta: [
            'id' => 1081,
            'image_url' => 'https://www.palegis.us/resources/images/members/200/1081.jpg',
        ]);

        $this->assertNull($legislator->image());
    }

    public function test_legislator_image_is_null_on_connection_failure(): void
    {
        Storage::fake('local');
        Http::fake(function () {
            throw new ConnectionException('Could not connect');
        });

        $legislator = new Legislator(meta: [
            'id' => 1081,
            'image_url' => 'https://www.palegis.us/resources/images/members/200/1081.jpg',
        ]);

        $this->assertNull($legislator->image());
    }
}
