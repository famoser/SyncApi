using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SqliteWrapper.Attributes;
using Famoser.SyncApi.Models.Interfaces;
using GalaSoft.MvvmLight;

namespace Famoser.SyncApi.Tests.Models
{
    public class NoteModel : ObservableObject, ISyncModel
    {
        [EntityMap]
        public string Content { get; set; }
        private Guid Id { get; set; }

        public Guid GetId()
        {
            return Id;
        }

        public void SetId(Guid id)
        {
            Id = id;
        }

        public string GetUniqeIdentifier()
        {
            return "notes";
        }

        public string GetGroupIdentifier()
        {
            return "all";
        }
    }
}
