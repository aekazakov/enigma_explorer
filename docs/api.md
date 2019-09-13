# Developers' Guide to ENIGMA Explorer

## Overview

[ENIGMA Explorer](http://isolates.genomics.lbl.gov), also known as the ENIGMA Isolates and Growth Curves Browser, is a web-based tool for querying and visualizing the [ENIGMA](https://enigma.lbl.gov/) collection, focusing on the isolate and growth curve data, also providing a pithy analytical toolkit.

The ENIGMA Explorer is intent on supporting the high-throughput isolation effort of ENIGMA, easing the pain in managing 16s sequences and phenotyping in the process. The database is largely accumulated by [Arkin's Lab](http://genomics.lbl.gov/) and [Deutschbauer's Lab](https://enigma.lbl.gov/deutschbauer-adam/), managed by DOE'S Lawrence Berkeley National Laboratory (Berkeley Lab).

## Maintainers

[Yujia Liu](mailto:yujialiu@lbl.gov) and [Lauren Lui](mailto:lmlui@lbl.gov) from Arkin's Lab.

## ENIGMA Explorer API v1

The ENIGMA Explorer API allows developers to query the desired isolates and growth data, and accessing advanced facilities like online BLAST. At the current stage, ENIGMA Explorer serves as a open but read-only data portal, interpreted as (1)Any users can get access to the ENIGMA collection without authorization and (2)Permissions to modify the ENIGMA collection is open to no users.

In general, the ENIGMA Explorer API uses HTTP POST and GET requests with JSON responses and optionally with JSON arguments. We recommend using `curl` or the GUI tool [Postman](https://www.getpostman.com) for testing and debug. No authorization is required. Base URL is `http://isolates.genomics.lbl.gov/api/:ver`, in which `:ver` is the version token, currently `v1`.

### Isolates

Retrieve the metadata and 16s rRNA sequence of ENIGMA isolates. The facilities do not provide full genome sequences nor isolates from FEBA collections.

#### /isolates/id

- Description

  Get the metadata and 16s sequence of an isolate by its id.

- URL Structure

  `http://isolates.genomics.lbl.gov/api/v1/isolates/id/:id`

- Method

  `GET`

- Example

  ```sh
  curl -X GET http://isolates.genomics.lbl.gov/api/v1/isolates/id/1
  ```

- Parameters

  No parameters required.

- Return

  **Code:** 200

  **Content:**

  ```json
  {
    "id": 1,
    "isolate_id": "FW305-130",
    "condition": "Sediment extract to 1/25 LB, aerobic, 30°C",
    "order": "Actinomycetales",
    "closest_relative": "Nocardia coeliaca strain DSM 44595",
    "similarity": 99,
    "date_sampled": "03/02/15",
    "sample_id": "FW305-03-02-15",
    "lab": "Chakraborty",
    "campaign": "Different Carbon Input Enrichment Isolates",
    "rrna": "GCAGTCGAGCGGTAAGGCCTTTCGGGGT..."
  }
  ```

  | Key | Type | Description |
  | :--- | :--- | :--- |
  | **id** | *UInt64* | id of the isolate |
  | **isolate_id** | *String* | The ENIGMA label for the isolate |
  | **condition** | *String* | The condition in which the strain is isolated |
  | **order** | *String* | Phylogenic order the isolate is assigned to. Notice that due to different pipelines used when collecting the data, even the isolates within the same genus can be assigned to different orders |
  | **closest\_relative** | *String* | The closest relative assigned by 16s sequence similarity |
  | **similarity** | *UFloat64* | 16 sequence similarity between the isolate and its closest relative, in percent |
  | **date\_sampled** | *Timestamp(format="%m/%d/%y")* | The date when the isolate is sampled |
  | **sample\_id** | *String* | Id of the sample |
  | **lab** | *String* | The lab where the strain was isolated |
  | **campaign** | *String* | In which campaign was the stain isolated |
  | **rrna** | *String* | Full-length 16s sequence of the isolate |

- Error

  Example: Non-numeric id encountered

  **Code:** 400

  **Content**

  ```json
  {
    "message": "Bad inquery"
  }
  ```

  | Key | Type | Description |
  | :--- | :--- | :--- |
  | **message** | *String* | Error message |

#### /isolates/isoid

- Description

  Search isolates by isolate id (the ENIGMA label), e.g. FW305-130

- URL Structure

  `http://isolates.genomics.lbl.gov/api/v1/isolates/isoid/:isoid`

- Method

  `GET`

- Example

  ```sh
  curl -X GET http://isolates.genomics.lbl.gov/api/v1/isolates/isoid/FW305-130
  ```

- Parameters

  No parameters required.

- Return and error

  Same as [/isolates/id](#/isolates/id)

#### /isolates/keyword

- Description

  Query the isolates by partially matching the keyword. Fields can be matched include `isolate_id`, `closest_relative` and `order`. Query length is limited to greater than 2 characters.

- URL Structure

  `http://isolates.genomics.lbl.gov/api/v1/isolates/keyword/:keyword`

- Method

  `GET`

- Example

  ```sh
  curl -X GET http://isolates.genomics.lbl.gov/api/v1
  ```

- Parameters

  No parateters required.

- Return

  **Code:** 200

  **Content:**

  ```json
  [
    {
      "id": 2,
      "isolate_id": "FW305-BF6",
      "condition": "filter on  1/25 R2A, aerobic, aphotic, 25°C",
      "order": "Bacillales",
      "closest_relative": "Bacillus acidicelar strain CBD 119",
      "similarity": 99,
      "date_sampled": "03/02/15",
      "sample_id": "FW305-03-02-15",
      "lab": "Chakraborty",
      "campaign": "Biofilm Campaign"
    },
    {
      "id": 3,
      "isolate_id": "FW104-L1",
      "condition": "LB, aerobic, 30°C",
      "order": "Bacillales",
      "closest_relative": "Bacillus anthracis str. Ames",
      "similarity": 99,
      "date_sampled": "11/14/12",
      "sample_id": "FW104-67-11-14-12",
      "lab": "Chakraborty",
      "campaign": "Oak Ridge Isolates"
    },
    // ...
  ]
  ```

  | Key | Type | Description |
  | :--- | :--- | :--- |
  | None | *List of (IsolateMeta)* | The metadata of matched isolate. The keys are similar to `isolates/id` but `rrna` field for 16s sequence is omitted |

  > IsolateMeta

    | Key | Type | Description |
    | :--- | :--- | :--- |
    | **id** | *UInt64* | id of the isolate |
    | **isolate_id** | *String* | The ENIGMA label for the isolate |
    | **condition** | *String* | The condition in which the strain is isolated |
    | **order** | *String* | Phylogenic order the isolate is assigned to. Notice that due to different pipelines used when collecting the data, even the isolates within the same genus can be assigned to different orders |
    | **closest\_relative** | *String* | The closest relative assigned by 16s sequence similarity |
    | **similarity** | *UFloat64* | 16 sequence similarity between the isolate and its closest relative, in percent |
    | **date\_sampled** | *Timestamp(format="%m/%d/%y")* | The date when the isolate is sampled |
    | **sample\_id** | *String* | Id of the sample |
    | **lab** | *String* | The lab where the strain was isolated |
    | **campaign** | *String* | In which campaign was the stain isolated |

- Error

  Example: Query string shorter than 3 charasters

  **Code:** 400

  **Content**

  ```json
  {
  "message": "Too short query keyword"
  }
  ```

  | Key | Type | Description |
  | :--- | :--- | :--- |
  | **message** | *String* | Error message |

#### /path1/path2

- Description
- URL Structure

  `http://isolates.genomics.lbl.gov/api/v1`

- Method

  `GET`

- Example

  ```sh
  curl -X GET http://isolates.genomics.lbl.gov/api/v1
  ```

- Parameters
- Return

  **Code:** 200

  **Content:**

  ```json
  ```

  | Key | Type | Description |
  | :--- | :--- | :--- |

- Error

  Example:

  **Code:** 400

  **Content**

  ```json
  {
    "message": ""
  }
  ```

  | Key | Type | Description |
  | :--- | :--- | :--- |
  | **message** | *String* | Error message |