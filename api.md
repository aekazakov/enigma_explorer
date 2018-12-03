# ENIGMA

## Isolates

Current stage APIs focus only on inqury of isolates information, as part of the [ENIGMA](http://enigma.lbl.gov/) project.

### Select isolates by isolate id

---

- **URL**

/api/v1/isolates/isoid/:isolate\_id

- **Method**

`GET`

- **URL Params**

**Required**
`isolate_id = [string]`

- **Success  Response**

Notice that an empty response is also 200

**Code:** 200
**Content:**
```
{ "id": 110,
  "isolate_id": "FW305-132",
  "condition": "1\/25 R2A, aerobic, 30C",
  "phylogeny": "Burkholderiales",
  "closest_relative": "Cupriavidus basilensis strain DSM 11853",
  "similarity": 99,
  "date_sampled": "3\/2\/15",
  "sample_id": "FW305",
  "lab": "Chakraborty",
  "campaign": "Different Carbon Input Enrichment Isolates" }
```

- **Error Response**

Happens when duplication isolates id in database:

**Code:** 404
**Content**
```
{ "message": "Unexpected data encontered" }
```

- **Simple Call:**

```
curl -i -X GET http://localhost:8000/api/v1/isolates/FW305-132
```

### Select isolates by ID

----

- **URL**

/api/v1/isolates/id/:id

- **Method**

`GET`

- **URL Params**

**Required**
`id = [integer]`

- **Success Response**

**Code:** 200
**Content:**

- **Error Response**

Happens when query id is not integer:

**Code:** 400
**Content:**

```
{ "message": "Bad inquery" }
```

- **Simple Call:**

```
curl -i -X GET http://localhost:8000/api/v1/isolates/id/1
```

### Select isolates by multiple keywords

Accepts **isolate id**, **order** and **closest relative** (include the binomial name) as URL parameter, but not id. The keyword is NOT case sensitive. Partial keyword is accepted, but too short keyword (less than 3 letters) will be filtered out for they are too vague.

Note this API do NOT return 16s rRNA sequence, to save data. Use `isoid`, `id` or `16s` API for 16s rRNA sequence.

----

- **URL**

/api/v1/isolates/keyword/:keyword

- **Method**

`GET`

- **URL Params**

**Required**
`keyword = [string]`

- **Success Response**

**Code:** 200
**Content:**

- **Error Response**

Happens when query keyword is too short (< 3 letters)

**Code:** 400
**Content:**

```
{ "message": "Too short query keyword" }
```

- **Simple Call:**

```
curl -i -X GET http://localhost:8000/api/v1/isolates/keyword/Pseudomonas
```

### Get match number of certain keyword

Keyword convention is the same as above, but only returns the number.

----

- **URL**

/api/v1/isolates/count/:keyword

- **Method**

`GET`

- **URL Params**

**Required**
`keyword = [string]`

- **Success Response**

**Code:** 200
**Content:**
```
{count: 20}
```

- **Error Response**

Happens when query keyword is too short (< 3 letters)

**Code:** 400
**Content:**

```
{ "message": "Too short query keyword" }
```

- **Simple Call:**

```
curl -i -X GET http://localhost:8000/api/v1/isolates/keyword/Pseudomonas
```
