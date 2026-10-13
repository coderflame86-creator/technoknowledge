# Form contract (website → Google Apps Script)

**Endpoint:** the Apps Script web app URL in `frontend/js/api.js` (`ENDPOINT`, ends with `/exec`).
Set-up steps: `backend/google-apps-script/SETUP-GUIDE.md`.

**Request:** `POST`, header `Content-Type: text/plain;charset=utf-8`, body = JSON.
(`text/plain` avoids a CORS pre-flight, which Apps Script does not answer.)

**Response (JSON):**
- Success: `{ "ok": true }`
- Problem: `{ "ok": false, "message": "text to show the visitor" }`

Every request includes:

| Field | Meaning |
|---|---|
| `form` | Sheet tab name: `Camp Registrations`, `Proposal Requests`, `AITEP Starter Bookings` or `Contact Messages` |
| `page` | Page the form was sent from, e.g. `/pages/lms.html` |
| `sentAt` | Time in the visitor's browser (ISO) |
| `elapsed` | Milliseconds since the page opened (under 2,500 = treated as a bot) |
| `consent` | `true` when the "you may contact me" box is ticked |

Lists (checkboxes, chosen courses) arrive as arrays and are saved as "A, B, C".
Every submission is saved to its tab and emailed to **technoknowledge19@gmail.com**.

## Camp Registrations (`campRegistration`, Techno Camps page)
| Field | Required |
|---|---|
| parentName | yes |
| whatsapp | yes |
| childName | yes |
| childAge | yes |
| courses (array) | no |
| classType (`1-to-1` / `Group`) | yes on the form |
| city, source, notes | no |

## Proposal Requests (`proposalRequest`)
Used by Request a proposal, ITEC curriculum, LMS, Robotics & IoT and Monitoring & Evaluation.
| Field | Required |
|---|---|
| program (which page, e.g. `ITEC`, `LMS`, `General proposal request`) | set by the page |
| interests (array, Request a proposal page) | yes on that page |
| interest (one option, other pages) | yes on those pages |
| name | yes |
| institution | yes |
| whatsapp | yes |
| role, city | yes on the form |
| email, teachers, students, message | no |

## AITEP Starter Bookings (`aitepStarter`, AITEP page)
| Field | Required |
|---|---|
| interest (`Free Starter session` / `Full AITEP proposal` / `AI curriculum for students`) | yes on the form |
| name, institution, whatsapp | yes |
| role, city | yes on the form |
| teachers, message | no |

## Contact Messages (`contact`, Contact page)
| Field | Required |
|---|---|
| name | yes |
| whatsapp | yes |
| message | yes |
| iam, subject | yes on the form |
| email | no |

"Yes on the form" = checked in the browser by `forms.js`; the script itself only insists on the
fields marked "yes", so a small form change never makes submissions fail.
