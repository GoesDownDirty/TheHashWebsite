CREATE INDEX COMPOSITE1_idx ON HARINGS(HARINGS_HASHER_KY, HARINGS_HASH_KY);
CREATE INDEX COMPOSITE1_idx ON HASHERS(HASHER_KY, HASHER_NAME);
CREATE INDEX COMPOSITE1_idx ON HASHES(HASH_KY, KENNEL_KY);
CREATE INDEX COMPOSITE2_idx ON HASHES(HASH_KY, EVENT_DATE, KENNEL_KY);
CREATE INDEX COMPOSITE3_idx ON HASHES(EVENT_DATE, HASH_KY, KENNEL_KY);
CREATE INDEX COMPOSITE1_idx ON HASHES_TAG_JUNCTION(HASHES_KY, HASHES_TAGS_KY);
CREATE INDEX COMPOSITE1_idx ON HASHES_TAGS(HASHES_TAGS_KY, TAG_TEXT);
CREATE INDEX COMPOSITE1_idx ON HASHINGS(HASHER_KY, HASH_KY);

